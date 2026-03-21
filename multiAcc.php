<?php

error_reporting(0);
date_default_timezone_set("Asia/Jakarta");

function extractBetweenTags($startTag, $endTag, $html)
{
    $startPos = strpos($html, $startTag);
    if ($startPos === false) {
        return ''; // or handle the error as needed
    }

    $start = $startPos + strlen($startTag);
    $endPos = strpos($html, $endTag, $start);

    if ($endPos === false) {
        return ''; // Or handle differently
    }
    return substr($html, $start, $endPos - $start);
}


function printNewLine()
{
    echo "\n";
}

function printDoubleNewLine()
{
    printNewLine();
    printNewLine();
}

function clearLine()
{
    echo "\r                                      \r";
}

function printWithColor($text, $colorCode)
{
    echo colorize($text, $colorCode);
}

function sleepAndClear()
{
    sleep(2);
    clearLine();
}


function printWithAnimatedColor($text, $colorCode)
{
    echo animatedColorText($text, $colorCode);
}

function animatedColorText($text, $color)
{
    $output = '';
    $textChars = str_split($text);
    foreach ($textChars as $char) {
        $output .= colorize($char, $color);
        usleep(1500);
    }
    return $output;
}

function colorize($text, $color)
{
    $colorCodes = [
        'rw' => "\033[107m\033[1;31m", // White background, Red text
        'rt' => "\033[106m\033[1;31m", // Cyan background, Red text
        'ht' => "\033[0;30m",          // Black text
        'p3' => "\033[1;37m",          // White text
        'p'  => "\033[1;37m",          // White text (duplicate)
        'a'  => "\033[1;30m",          // Black text
        'm'  => "\033[1;31m",          // Red text
        'h'  => "\033[1;32m",          // Green text
        'k'  => "\033[1;33m",          // Yellow text
        'b'  => "\033[1;34m",          // Blue text
        'u'  => "\033[1;35m",          // Magenta text
        'c'  => "\033[1;36m",          // Cyan text
        'rr' => "\033[101m\033[1;37m", // Red background, White text
        'rg' => "\033[102m\033[1;34m", // Green background, Blue text
        'ry' => "\033[103m\033[1;30m", // Yellow background, Black text
        'rp1' => "\033[104m\033[1;37m", // Blue background, White text
        'rp2' => "\033[105m\033[1;37m", // Magenta background, White text
    ];

    //Random color (simplified, no '5' special case)
    if (!array_key_exists($color, $colorCodes)) {
        $randomColors = ['h', 'k', 'b', 'u', 'm'];
        $color = $randomColors[array_rand($randomColors)];
    }

    return $colorCodes[$color] . $text . "\033[0m"; // Reset color
}

// Constants (better than scattered magic strings)
const NEWLINE = "\n";
const DOUBLE_NEWLINE = "\n\n";
const TAB = "\t";
const CLEAR_LINE = "\r                                                              \r";

// Colors (avoid repeating escape codes)
const COLOR_RESET = "\033[0m";
const COLOR_BLACK = "\033[0;30m";
const COLOR_BOLD_BLACK = "\033[1;30m";
const COLOR_WHITE = "\033[0;37m";
const COLOR_BOLD_WHITE = "\033[1;37m";
const COLOR_RED = "\033[0;31m";
const COLOR_BOLD_RED = "\033[1;31m";
const COLOR_GREEN = "\033[92m"; // Bright Green
const COLOR_BOLD_GREEN = "\033[1;32m";
const COLOR_YELLOW = "\033[0;33m";
const COLOR_BOLD_YELLOW = "\033[1;33m";
const COLOR_BLUE = "\033[0;34m";
const COLOR_BOLD_BLUE = "\033[1;34m";
const COLOR_MAGENTA = "\033[0;35m";
const COLOR_BOLD_MAGENTA = "\033[1;35m";
const COLOR_CYAN = "\033[0;36m";
const COLOR_BOLD_CYAN = "\033[1;36m";

// Background Colors
const BG_BLUE = "\033[44m";
const BG_RED = "\033[41m";
const BG_YELLOW = "\033[43m";
const BG_CYAN = "\033[46m";
const BG_MAGENTA = "\033[45m";
const BG_GREEN = "\033[42m";
const BG_WHITE = "\033[47m";


// Symbols
const SYMBOL_SUCCESS = COLOR_BOLD_WHITE . "❖ " . COLOR_RESET;
const SYMBOL_INFO = COLOR_BOLD_BLUE . "☯ " . COLOR_RESET;
const SYMBOL_ARROW = COLOR_BOLD_RED . "➞ Succes " . COLOR_RESET;  //Consistent naming
const SYMBOL_SEPARATOR = COLOR_BOLD_RED . " / " . COLOR_RESET;
const SYMBOL_EMPTY = " ";
const SYMBOL_BALANCE = COLOR_BOLD_GREEN . "Available Balance" . COLOR_RESET;
const SYMBOL_REFERRAL = COLOR_BOLD_WHITE . "Link Reff" . COLOR_RESET;

// Banner and UI Functions
function printHorizontalLine()
{
    echo str_repeat(colorize('─', 'p3'), 50);
    printNewLine();
}


// Core cURL Functions (improved)
function curlRequest($url, $postData = '', $headers = [], $proxy = '', $useCookie = true)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_ENCODING => 'gzip', // Add gzip encoding
    ]);

    if ($useCookie) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    }

    if (!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        // Consider adding proxy type (CURLPROXY_SOCKS5, etc.) if needed
    }
    curl_setopt($ch, CURLOPT_HEADER, true); // keep this last

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Curl Error : " . $error_msg;  // consistent error handling.
    }

    // Split header and body, handle potential errors
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    return ['header' => $header, 'body' => $body, 'http_code' => $httpCode];
}

function curlGet($url, $headers)
{
    return curlRequest($url, '', $headers);
}

function curlPost($url, $postData, $headers)
{
    return curlRequest($url, $postData, $headers);
}

function saveData($filename, $data)
{
    if (!file_exists($filename)) {
        file_put_contents($filename, "[]");
    }
    $existingData = json_decode(file_get_contents($filename), true);
    $mergedData = array_merge($existingData, $data);
    file_put_contents($filename, json_encode($mergedData, JSON_PRETTY_PRINT));
}


// Encryption/Decryption Functions
function getKey($keyString)
{
    return hash('md5', $keyString, true);
}

function decryptData($encryptedData)
{
    $key = getKey("appWorldKey");
    $decrypted = openssl_decrypt(hex2bin($encryptedData), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return $decrypted;
}

function encryptData($data)
{
    $key = getKey('appWorldKey');
    $encrypted = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return bin2hex($encrypted);
}

function decryptData1($encryptedData)
{
    $key = b"\xc0\xf7\x07/\\r\xcavF\x96\xde.F\x87\x1d\x1c"; // Raw bytes
    $decrypted = openssl_decrypt(hex2bin($encryptedData), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return $decrypted;
}

function encryptData1($data)
{
    $key = b"\xc0\xf7\x07/\\r\xcavF\x96\xde.F\x87\x1d\x1c";
    $encrypted = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return bin2hex($encrypted);
}

// Timer function (fixed)
function timer($seconds = null)
{
    $g = COLOR_BOLD_BLACK;
    $w = COLOR_BOLD_WHITE;

    if ($seconds === null) {
        $seconds = rand(2, 2); // the original code did nothing.
    }

    for ($i = $seconds; $i >= 0; $i--) {
        clearLine();
        echo $w . "[$g Please wait => " . gmdate("H:i:s", $i) . "$w ]    ";
        sleep(1);  // only sleep for 1 second
        clearLine();
    }
}


// Main Script Logic

function run_for_apk($account, $apk)
{
    $datalogin = $account['datalogin'];
    $foreground = $account['foreground'];
    $mainURL = $apk['url'];
    $packageName = $apk['packageName'];


    $decryptedDatalogin = decryptData($datalogin);
    $dataloginData = json_decode($decryptedDatalogin, true);
    $deviceId = $dataloginData["deviceId"];

    $decryptedForeground = decryptData1($foreground);
    $foregroundData = json_decode($decryptedForeground, true);
    $advertisementId = $foregroundData["advertisementId"];

    $headersGivvy = [
        "currency:USD",
        "Connection:close",
        "language:English",
        "packageName:$packageName",
        "Content-Type:application/json; charset=utf-8",
        "Host:$mainURL",
        "User-Agent:okhttp/5.0.0-alpha.12"
    ];

    $headersFreeIpApi = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        'cache-control: max-age=0',
        'sec-ch-ua: "Not(A:Brand";v="99", "Google Chrome";v="133", "Chromium";v="133"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'upgrade-insecure-requests: 1',
        'sec-fetch-site: none',
        'sec-fetch-mode: navigate',
        'sec-fetch-user: ?1',
        'sec-fetch-dest: document',
        'accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'priority: u=0, i',
    ];

    $headersGivvyConfig = [
        "language:en",
        "isProduction:true",
        "packageName:$packageName",
        "Content-Type:application/json; charset=utf-8",
        "Host:givvy-general-config.herokuapp.com",
        "User-Agent:okhttp/5.0.0-alpha.12"
    ];


    // Login
    $loginUrl = "https://$mainURL/loginEstablished";
    $timestamp = round(microtime(true) * 1000);
    $loginData = '{"deviceType":"Android","deviceId":"' . $deviceId . '","verts":' . $timestamp . '}';
    $encryptedLoginData = encryptData($loginData);
    $loginPostData = '{"verificationCode":"' . $encryptedLoginData . '"}';
    $loginResponse = curlPost($loginUrl, $loginPostData, $headersGivvy);

    if (!is_array($loginResponse)) { //check if $loginResponse is valid
        clearLine();
        echo "Login Failed: " . $loginResponse . NEWLINE; //show error
        return; // Exit this run if login fails
    }
    $loginResponse = $loginResponse["body"];

    $username = extractBetweenTags('username":"', '"', $loginResponse);
    $credits =  explode(',', explode('credits":', $loginResponse)[1])[0];
    $userBalance = extractBetweenTags('userBalanceWithCurrency":"', '"', $loginResponse);
    $userId = extractBetweenTags('id":"', '"', $loginResponse);
    clearLine();
    echo SYMBOL_SUCCESS . SYMBOL_INFO . SYMBOL_ARROW . COLOR_BOLD_YELLOW . $username . " | Device ID (" . $deviceId . ")" . NEWLINE;
    clearLine();
    echo SYMBOL_SUCCESS . COLOR_BOLD_GREEN . SYMBOL_BALANCE . COLOR_BOLD_YELLOW . " " . $credits . SYMBOL_SEPARATOR . COLOR_BOLD_YELLOW . $userBalance . NEWLINE;
    clearLine();
    echo "Package Name: " . COLOR_BOLD_YELLOW . $packageName . NEWLINE;
    printHorizontalLine();


    // Reward Loop
    $limitCount = 0;
    while (true) {
        api:
        try {
            clearLine();
            echo "Getting IP";
            $urll = "https://freeipapi.com/api/json";
            $res = curlGet($urll, $headersFreeIpApi);
            $der = $res['body'];
            if (!is_array($res) || !isset($res['http_code'])) {
                clearLine();
                echo "getStatus cURL Error";
                sleep(5);
                goto api;
            }
            if ($res['http_code'] != 200) {
                clearLine();
                echo "getStatus HTTP Error " . $res['http_code'] . NEWLINE;
                sleep(5);
                goto api;
            }
        } catch (Exception $e) {
            clearLine();
            echo 'An Error: ' . $e->getMessage();
            sleep(2);
            goto api;
        }

        sendForegroundStatus:
        try {
            clearLine();
            echo "Sending Foreground Status";
            $message = '{"userId":"' . $userId . '","isWithVpn":true,"isOnWifi":false,"isRooted":false,"localIpAddress":"0.0.0.0","localData":' . $der . ',"simNumber":true,"packageName":"' . $packageName . '","advertisementId":"' . $advertisementId . '"}';
            $encryptedForegroundData = encryptData1($message);
            $foregroundPostData = '{"verificationCode":"' . $encryptedForegroundData . '"}';
            $res = curlPost("https://$mainURL/sendForegroundStatus", $foregroundPostData, $headersGivvyConfig);
        } catch (Exception $e) {
            clearLine();
            echo 'An Error: ' . $e->getMessage();
            sleep(2);
            goto sendForegroundStatus;
        }


        clearLine();
        echo "Getting Status Account";
        $message = '{"userId":"' . $userId . '","date":"' . round(microtime(true) * 1000) . '","advertisementId":"' . $advertisementId . '","usedVpnInSession":true,"currentImpressionsList":[{"publisherRevenue":0.0013112999999999998,"country":"ID","adUnitFormat":"Interstitial","networkName":"Meta Audience Network bidder","hasBeenClicked":false,"network":"fyber","priceAccuracy":"PROGRAMMATIC","jsonString":"{\"advertiser_domain\":null,\"campaign_id\":null,\"country_code\":\"ID\",\"creative_id\":null,\"currency\":\"USD\",\"demand_source\":\"Meta Audience Network bidder\",\"impression_depth\":1,\"impression_id\":\"8ab1cf83-ecd5-4c42-8a28-40323e979e10\",\"request_id\":\"8ab1cf83-ecd5-4c42-8a28-40323e979e10\",\"net_payout\":0.0013112999999999998,\"network_instance_id\":\"1508187396582385_1508188736582251\",\"price_accuracy\":2,\"placement_type\":1,\"rendering_sdk\":\"Meta Audience Network\",\"rendering_sdk_version\":\"6.16.0\",\"variant_id\":\"2164247\"}"}]}';
        $dya = encryptData1($message);
        $data2 = '{"verificationCode":"' . $dya . '"}';
        $url2 = "https://givvy-general-config.herokuapp.com/getStatus";
        $res = curlPost($url2, $data2, $headersGivvyConfig);


        timer(rand(5, 8));
        claim:
        try {
            clearLine();
            echo "Claiming Reward";
            $message = '{"userId":"' . $userId . '","verts":' . round(microtime(true) * 1000) . '}';
            $dya = encryptData($message);
            $data = '{"verificationCode":"' . $dya . '"}';
            $url = "https://$mainURL/getPresentReward";
            $res = curlPost($url, $data, $headersGivvy);
            if (!is_array($res) || !isset($res['http_code'])) {
                clearLine();
                echo "getStatus cURL Error";
                sleep(5);
                goto claim;
            }
            if ($res['http_code'] != 200) {
                clearLine();
                echo "getStatus HTTP Error " . $res['http_code'] . NEWLINE;
                sleep(5);
                goto claim;
            }
            $res = json_decode($res['body'], true);
        } catch (Exception $e) {
            clearLine();
            echo 'An Error: ' . $e->getMessage();
            sleep(2);
            goto claim;
        }
        if ($res['statusCode'] == 200) {
            $credits = $res["result"]["credits"];
            $earn = $res["result"]["earnCredits"];
            if ($earn <= 25) {
                if ($limitCount >= 3) {
                    clearLine();
                    echo "Limit Tercapai..!\n";
                    sleep(1);
                    break; //Break inner loop (per account, per APK)
                } else {
                    $limitCount = $limitCount + 1;
                }
            } else {
                $limitCount = 0;
            }
            clearLine();
            echo "earned > $earn points > $credits\n";
        } else {
            clearLine();
            echo "Error: " . $res['body'] . "\t Trying Again...\n";
            continue; //Continue inner loop
        }
    }
}


if (!file_exists("config.json")) {
    // Interactive setup if config.json is missing
    $configData = ["accounts" => [], "apks" => []];
    $accountCount = (int)readline(COLOR_BOLD_GREEN . "How many accounts do you want to add? " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
    for ($i = 0; $i < $accountCount; $i++) {
        clearLine();
        echo "Adding Account " . ($i + 1) . NEWLINE;
        $datalogin = readline(COLOR_BOLD_GREEN . "Input Data Login " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
        $foreground = readline(COLOR_BOLD_GREEN . "Input AD ID " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
        $configData["accounts"][] = [
            "datalogin" => $datalogin,
            "foreground" => $foreground
        ];
    }

    $apkCount = (int)readline(COLOR_BOLD_GREEN . "How many APKs do you want to add? " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
    for ($i = 0; $i < $apkCount; $i++) {
        clearLine();
        echo "Adding APK " . ($i + 1) . NEWLINE;
        $url = readline(COLOR_BOLD_GREEN . "Input URL " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
        $packageName = readline(COLOR_BOLD_GREEN . "Input Package Name " . COLOR_BOLD_RED . ": " . COLOR_BOLD_YELLOW);
        $configData["apks"][] = [
            "url" => $url,
            "packageName" => $packageName
        ];
    }
    file_put_contents("config.json", json_encode($configData, JSON_PRETTY_PRINT));
}



// Load Configuration
$config = json_decode(file_get_contents("config.json"), true);

// Main Loop: Iterate through accounts and APKs
foreach ($config['accounts'] as $account) {
    foreach ($config['apks'] as $apk) {
        run_for_apk($account, $apk);
    }
}

?>