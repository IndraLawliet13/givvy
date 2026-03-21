#!/usr/bin/env python3
import os, sys, time, random, json, hashlib, requests, threading, concurrent.futures
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad, unpad
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Global lock untuk sinkronisasi output
print_lock = threading.Lock()

def safe_print(*args, **kwargs):
    with print_lock:
        print(*args, **kwargs)

def clear_terminal():
    if os.name == 'nt':
        os.system('cls')
    else:
        os.system('clear')

# Global stop event buat menghentikan thread secara terkontrol
stop_event = threading.Event()

# -------------------- CONSTANTS & COLOR CODES --------------------
NEWLINE = "\n"
DOUBLE_NEWLINE = "\n\n"
TAB = "\t"
CLEAR_LINE = "\r" + " " * 80 + "\r"

COLOR_CODES = {
    'rw': "\033[107m\033[1;31m",  # White background, Red text
    'rt': "\033[106m\033[1;31m",  # Cyan background, Red text
    'ht': "\033[0;30m",           # Black text
    'p3': "\033[1;37m",           # White text
    'p': "\033[1;37m",            # White text
    'a': "\033[1;30m",            # Black text
    'm': "\033[1;31m",            # Red text
    'h': "\033[1;32m",            # Green text
    'k': "\033[1;33m",            # Yellow text
    'b': "\033[1;34m",            # Blue text
    'u': "\033[1;35m",            # Magenta text
    'c': "\033[1;36m",            # Cyan text
    'rr': "\033[101m\033[1;37m",  # Red background, White text
    'rg': "\033[102m\033[1;34m",  # Green background, Blue text
    'ry': "\033[103m\033[1;30m",  # Yellow background, Black text
    'rp1': "\033[104m\033[1;37m", # Blue background, White text
    'rp2': "\033[105m\033[1;37m", # Magenta background, White text
    'reset': "\033[0m",
    'black': "\033[0;30m",
    'bold_black': "\033[1;30m",
    'white': "\033[0;37m",
    'bold_white': "\033[1;37m",
    'red': "\033[0;31m",
    'bold_red': "\033[1;31m",
    'green': "\033[92m",
    'bold_green': "\033[1;32m",
    'yellow': "\033[0;33m",
    'bold_yellow': "\033[1;33m",
    'blue': "\033[0;34m",
    'bold_blue': "\033[1;34m",
    'magenta': "\033[0;35m",
    'bold_magenta': "\033[1;35m",
    'cyan': "\033[0;36m",
    'bold_cyan': "\033[1;36m",
}

SYMBOL_SUCCESS = COLOR_CODES['bold_white'] + "❖ " + COLOR_CODES['reset']
SYMBOL_INFO = COLOR_CODES['bold_blue'] + "☯ " + COLOR_CODES['reset']
SYMBOL_ARROW = COLOR_CODES['bold_red'] + "➞ Succes " + COLOR_CODES['reset']
SYMBOL_SEPARATOR = COLOR_CODES['bold_red'] + " / " + COLOR_CODES['reset']
SYMBOL_BALANCE = COLOR_CODES['bold_green'] + "Available Balance" + COLOR_CODES['reset']
SYMBOL_REFERRAL = COLOR_CODES['bold_white'] + "Link Reff" + COLOR_CODES['reset']

# -------------------- UTILITY FUNCTIONS --------------------
def extract_between_tags(start_tag, end_tag, text):
    start_pos = text.find(start_tag)
    if start_pos == -1:
        return ""
    start = start_pos + len(start_tag)
    end_pos = text.find(end_tag, start)
    if end_pos == -1:
        return ""
    return text[start:end_pos]

def print_new_line():
    safe_print()

def print_double_new_line():
    print_new_line()
    print_new_line()

def clear_line():
    # Gunakan safe_print agar konsisten
    with print_lock:
        sys.stdout.write(CLEAR_LINE)
        sys.stdout.flush()

def colorize(text, color):
    if color not in COLOR_CODES:
        random_colors = ['h', 'k', 'b', 'u', 'm']
        color = random.choice(random_colors)
    return COLOR_CODES[color] + text + COLOR_CODES['reset']

def print_with_color(text, color):
    safe_print(colorize(text, color))

def animated_color_text(text, color):
    for char in text:
        with print_lock:
            sys.stdout.write(colorize(char, color))
            sys.stdout.flush()
        time.sleep(0.0015)
    safe_print()  # newline di akhir

def print_with_animated_color(text, color):
    animated_color_text(text, color)

# -------------------- HTTP REQUEST FUNCTIONS --------------------
session = requests.Session()
session.verify = False  # Nonaktifin verifikasi SSL

def curl_request(url, post_data='', headers={}, proxy='', use_cookie=True):
    try:
        proxies = {"http": proxy, "https": proxy} if proxy else None
        if post_data:
            response = session.post(url, data=post_data, headers=headers, proxies=proxies, timeout=60)
        else:
            response = session.get(url, headers=headers, proxies=proxies, timeout=60)
        return {
            'header': response.headers,
            'body': response.text,
            'http_code': response.status_code
        }
    except Exception as e:
        return "Curl Error : " + str(e)

def curl_get(url, headers):
    return curl_request(url, headers=headers)

def curl_post(url, post_data, headers):
    return curl_request(url, post_data=post_data, headers=headers)

def save_data(filename, data):
    if not os.path.exists(filename):
        with open(filename, "w") as f:
            f.write("[]")
    with open(filename, "r") as f:
        try:
            existing_data = json.load(f)
        except:
            existing_data = []
    merged_data = existing_data + data
    with open(filename, "w") as f:
        json.dump(merged_data, f, indent=4)

# -------------------- ENCRYPTION/DECRYPTION FUNCTIONS --------------------
def get_key(key_string):
    return hashlib.md5(key_string.encode()).digest()

def decryptData(encryptedData):
    key = get_key("appWorldKey")
    try:
        cipher = AES.new(key, AES.MODE_ECB)
        decrypted = cipher.decrypt(bytes.fromhex(encryptedData))
        decrypted = unpad(decrypted, AES.block_size)
        return decrypted.decode()
    except Exception as e:
        return ""

def encryptData(data):
    key = get_key("appWorldKey")
    cipher = AES.new(key, AES.MODE_ECB)
    padded_data = pad(data.encode(), AES.block_size)
    encrypted = cipher.encrypt(padded_data)
    return encrypted.hex()

def decryptData1(encryptedData):
    key = b"\xc0\xf7\x07/\\r\xcavF\x96\xde.F\x87\x1d\x1c"
    try:
        cipher = AES.new(key, AES.MODE_ECB)
        decrypted = cipher.decrypt(bytes.fromhex(encryptedData))
        decrypted = unpad(decrypted, AES.block_size)
        return decrypted.decode()
    except Exception as e:
        return ""

def encryptData1(data):
    key = b"\xc0\xf7\x07/\\r\xcavF\x96\xde.F\x87\x1d\x1c"
    cipher = AES.new(key, AES.MODE_ECB)
    padded_data = pad(data.encode(), AES.block_size)
    encrypted = cipher.encrypt(padded_data)
    return encrypted.hex()

# -------------------- TIMER FUNCTION --------------------
def timer(seconds=None):
    if seconds is None:
        seconds = random.randint(2, 2)
    for i in range(seconds, -1, -1):
        if stop_event.is_set():
            break
        clear_line()
        time_str = time.strftime("%H:%M:%S", time.gmtime(i))
        with print_lock:
            sys.stdout.write(COLOR_CODES['bold_white'] + "[ " + COLOR_CODES['bold_black'] + "Please wait => " + time_str + COLOR_CODES['bold_white'] + " ]    ")
            sys.stdout.flush()
        time.sleep(1)
        clear_line()

# -------------------- MAIN FUNCTION: RUN FOR APK --------------------
def run_for_apk(account, apk):
    datalogin = account.get('datalogin')
    foreground = account.get('foreground')
    mainURL = apk.get('url')
    packageName = apk.get('packageName')
    version = apk.get('version')
    
    decryptedDatalogin = decryptData(datalogin)
    try:
        dataloginData = json.loads(decryptedDatalogin)
    except:
        dataloginData = {}
    deviceId = dataloginData.get("deviceId", "")
    
    decryptedForeground = decryptData1(foreground)
    try:
        foregroundData = json.loads(decryptedForeground)
    except:
        foregroundData = {}
    advertisementId = foregroundData.get("advertisementId", "")
    
    headersGivvy = {
        "currency": "USD",
        "Connection": "close",
        "language": "English",
        "version": version,
        "packageName": packageName,
        "Content-Type": "application/json; charset=utf-8",
        "Host": mainURL,
        "User-Agent": "okhttp/5.0.0-alpha.12"
    }
    
    headersFreeIpApi = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'cache-control': 'max-age=0',
        'sec-ch-ua': '"Not(A:Brand";v="99", "Google Chrome";v="133", "Chromium";v="133"',
        'sec-ch-ua-mobile': '?0',
        'sec-ch-ua-platform': '"Windows"',
        'upgrade-insecure-requests': '1',
        'sec-fetch-site': 'none',
        'sec-fetch-mode': 'navigate',
        'sec-fetch-user': '?1',
        'sec-fetch-dest': 'document',
        'accept-language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'priority': 'u=0, i',
    }
    
    headersGivvyConfig = {
        "language": "ENGLISH",
        "versionName": version,
        "isProduction": "true",
        "packageName": packageName,
        "Content-Type": "application/json; charset=utf-8",
        "Host": "givvy-general-config.herokuapp.com",
        "Connection": "Keep-Alive",
        "Accept-Encoding": "gzip",
        "User-Agent": "okhttp/5.0.0-alpha.12"
    }
    
    # ---- Login Process ----
    loginUrl = f"https://{mainURL}/loginEstablished"
    timestamp = round(time.time() * 1000)
    loginData = json.dumps({
        "deviceType": "Android",
        "version": version,
        "deviceId": deviceId,
        "verts": timestamp
    })
    encryptedLoginData = encryptData(loginData)
    loginPostData = json.dumps({"verificationCode": encryptedLoginData})
    loginResponse = curl_post(loginUrl, loginPostData, headersGivvy)
    
    if not isinstance(loginResponse, dict) or 'body' not in loginResponse:
        clear_line()
        safe_print("Login Failed:", loginResponse)
        return
    
    loginResponseBody = loginResponse["body"]
    username = extract_between_tags('username":"', '"', loginResponseBody)
    try:
        credits_part = loginResponseBody.split('credits":', 1)[1]
        credits = credits_part.split(',', 1)[0]
    except:
        credits = ""
    userBalance = extract_between_tags('userBalanceWithCurrency":"', '"', loginResponseBody)
    userId = extract_between_tags('id":"', '"', loginResponseBody)
    
    clear_line()
    safe_print(SYMBOL_SUCCESS + SYMBOL_INFO + SYMBOL_ARROW + COLOR_CODES['bold_yellow'] + f"{username} | Device ID ({deviceId})")
    clear_line()
    safe_print(SYMBOL_SUCCESS + COLOR_CODES['bold_green'] + SYMBOL_BALANCE + COLOR_CODES['bold_yellow'] + f" {credits}" + SYMBOL_SEPARATOR + COLOR_CODES['bold_yellow'] + f" {userBalance}")
    clear_line()
    safe_print(COLOR_CODES['bold_green'] + "Package Name: " + COLOR_CODES['bold_yellow'] + packageName + COLOR_CODES['reset'])
    safe_print("-" * 50)
    
    # ---- Reward Loop ----
    limitCount = 0
    while not stop_event.is_set():
        # Get IP
        while not stop_event.is_set():
            try:
                clear_line()
                safe_print("Getting IP", end="", flush=True)
                url_ip = "https://freeipapi.com/api/json"
                res = curl_get(url_ip, headersFreeIpApi)
                der = res.get('body', '')
                if not isinstance(res, dict) or 'http_code' not in res:
                    clear_line()
                    safe_print("getStatus cURL Error")
                    time.sleep(5)
                    continue
                if res.get('http_code') != 200:
                    clear_line()
                    safe_print("getStatus HTTP Error", res.get('http_code'))
                    time.sleep(5)
                    continue
                break
            except Exception as e:
                clear_line()
                safe_print("An Error:", str(e))
                time.sleep(2)
        
        # Send Foreground Status
        while not stop_event.is_set():
            try:
                clear_line()
                safe_print("Sending Foreground Status", end="", flush=True)
                message = json.dumps({
                    "userId": userId,
                    "isRooted": False,
                    "packageName": packageName,
                    "advertisementId": advertisementId
                })
                encryptedForegroundData = encryptData1(message)
                foregroundPostData = json.dumps({"verificationCode": encryptedForegroundData})
                res = curl_post(f"https://givvy-general-config.herokuapp.com/sendForegroundStatus", foregroundPostData, headersGivvyConfig)
                res_json = json.loads(res.get('body', '{}'))
                break
            except Exception as e:
                clear_line()
                safe_print("An Error:", str(e))
                time.sleep(2)
        result = res_json.get("result", {})
        if result["shouldForceClose"]:
            clear_line()
            safe_print("Unusual traffic detected!... This account will stop..")
            time.sleep(1)
            break
        
        # Get Status Account
        try:
            clear_line()
            safe_print("Getting Status Account", end="", flush=True)
            message = json.dumps({
                "userId": userId,
                "date": round(time.time() * 1000),
                "advertisementId": advertisementId,
                "usedVpnInSession": True,
                "currentImpressionsList": [
                    {
                        "publisherRevenue": 0.0013112999999999998,
                        "country": "ID",
                        "adUnitFormat": "Interstitial",
                        "networkName": "Meta Audience Network bidder",
                        "hasBeenClicked": False,
                        "network": "fyber",
                        "priceAccuracy": "PROGRAMMATIC",
                        "jsonString": json.dumps({
                            "advertiser_domain": None,
                            "campaign_id": None,
                            "country_code": "ID",
                            "creative_id": None,
                            "currency": "USD",
                            "demand_source": "Meta Audience Network bidder",
                            "impression_depth": 1,
                            "impression_id": "8ab1cf83-ecd5-4c42-8a28-40323e979e10",
                            "request_id": "8ab1cf83-ecd5-4c42-8a28-40323e979e10",
                            "net_payout": 0.0013112999999999998,
                            "network_instance_id": "1508187396582385_1508188736582251",
                            "price_accuracy": 2,
                            "placement_type": 1,
                            "rendering_sdk": "Meta Audience Network",
                            "rendering_sdk_version": "6.16.0",
                            "variant_id": "2164247"
                        })
                    }
                ]
            })
            dya = encryptData1(message)
            data2 = json.dumps({"verificationCode": dya})
            url2 = "https://givvy-general-config.herokuapp.com/getStatus"
            res_status = curl_post(url2, data2, headersGivvyConfig)
        except Exception as e:
            clear_line()
            safe_print("An Error:", str(e))
        
        timer(random.randint(3, 5))
        
        # Claim Reward
        while not stop_event.is_set():
            try:
                clear_line()
                safe_print("Claiming Reward", end="", flush=True)
                message = json.dumps({
                    "userId": userId,
                    "verts": round(time.time() * 1000)
                })
                dya_claim = encryptData(message)
                data_claim = json.dumps({"verificationCode": dya_claim})
                url_claim = f"https://{mainURL}/getPresentReward"
                res_claim = curl_post(url_claim, data_claim, headersGivvy)
                if not isinstance(res_claim, dict) or 'http_code' not in res_claim:
                    clear_line()
                    safe_print("getStatus cURL Error")
                    time.sleep(5)
                    continue
                if res_claim.get('http_code') != 200:
                    clear_line()
                    safe_print("getStatus HTTP Error", res_claim.get('http_code'))
                    time.sleep(5)
                    continue
                res_json = json.loads(res_claim.get('body', '{}'))
                break
            except Exception as e:
                clear_line()
                safe_print("An Error:", str(e))
                time.sleep(2)
        
        if res_json.get('statusCode') == 200:
            result = res_json.get("result", {})
            credits = result.get("credits", "")
            earn = result.get("earnCredits", 0)
            balance = result.get("userBalanceDouble", 1)
            if earn <= 25:
                if limitCount >= 3:
                    clear_line()
                    safe_print("Limit Tercapai..!")
                    time.sleep(1)
                    break
                else:
                    limitCount += 1
            else:
                limitCount = 0
            clear_line()
            if credits >= 350000: safe_print(f"earned > {earn:<5} points > {credits:<7} (${balance:^8}) | {packageName:^50} [ Can Withdraw ]")
            else: safe_print(f"earned > {earn:<5} points > {credits:<7} (${balance:^8}) | {packageName:^50}")
        else:
            clear_line()
            safe_print("Error:", res_claim.get('body', ''), "\t Trying Again...")
            continue

# -------------------- MULTI-THREADING FUNCTION --------------------
def process_account(account, apks):
    # Jalankan setiap APK secara paralel untuk satu akun
    with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
        futures = [executor.submit(run_for_apk, account, apk) for apk in apks]
        concurrent.futures.wait(futures)

# -------------------- MAIN EXECUTION --------------------
def main():
    config_filename = "config.json"
    if not os.path.exists(config_filename):
        configData = {"accounts": [], "apks": []}
        try:
            accountCount = int(input(colorize("How many accounts do you want to add? : ", "bold_green")))
        except:
            accountCount = 0
        for i in range(accountCount):
            clear_line()
            safe_print(f"Adding Account {i+1}")
            datalogin = input(colorize("Input Data Login : ", "bold_green"))
            foreground = input(colorize("Input foreground: ", "bold_green"))
            configData["accounts"].append({
                "datalogin": datalogin.strip(),
                "foreground": foreground.strip()
            })
        try:
            apkCount = int(input(colorize("How many APKs do you want to add? : ", "bold_green")))
        except:
            apkCount = 0
        for i in range(apkCount):
            clear_line()
            safe_print(f"Adding APK {i+1}")
            url = input(colorize("Input URL : ", "bold_green"))
            packageName = input(colorize("Input Package Name : ", "bold_green"))
            versionAPK = input(colorize("Input APK Version : ", "bold_green"))
            configData["apks"].append({
                "url": url.strip(),
                "packageName": packageName.strip(),
                "version": versionAPK.strip()
            })
        with open(config_filename, "w") as f:
            json.dump(configData, f, indent=4)
    
    with open(config_filename, "r") as f:
        config = json.load(f)
    
    accounts = config.get("accounts", [])
    apks = config.get("apks", [])
    
    # Eksekusi tiap akun secara paralel (maksimal 5 akun sekaligus)
    with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
        futures = [executor.submit(process_account, account, apks) for account in accounts]
        concurrent.futures.wait(futures)

if __name__ == "__main__":
    try:
        clear_terminal()
        main()
    except KeyboardInterrupt:
        safe_print("\nScript dihentikan oleh user!")
        stop_event.set()
        os._exit(0)
