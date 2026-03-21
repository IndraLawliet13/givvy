# givvy

Automation scripts for the Givvy-style scratch-card reward workflow, preserved as a safe public showcase from a private working setup.

This public candidate intentionally keeps only the reusable code variants while excluding live encrypted account blobs, cookie jars, and duplicate deployment folders.

## What is included

- `multiAcc.py` - Python implementation
- `multiAcc.php` - PHP implementation
- `config.example.json` - safe example config shape
- `requirements.txt` - minimal Python dependencies
- `LICENSE`

## What is intentionally excluded

- real `config.json`
- real `cookie.txt`
- `cf.json`, `cf2.json`, `cf3.json`
- archived duplicate bundle `reff.zip`
- duplicated private folders used only for deployment variants

## Project shape

The code is built around the same pattern in both implementations:

1. load account blobs and APK metadata from `config.json`
2. decrypt stored mobile-app state fields
3. construct Givvy API headers and device identifiers
4. run per-account reward actions in parallel
5. persist only local runtime state when needed

## Setup

### Python path
```bash
pip install -r requirements.txt
cp config.example.json config.json
python3 multiAcc.py
```

### PHP path
```bash
cp config.example.json config.json
php multiAcc.php
```

## Config format

`config.json` contains two arrays:

- `accounts`
  - `datalogin`
  - `foreground`
- `apks`
  - `url`
  - `packageName`
  - `version`

Use `config.example.json` as the template only. Do not commit live values.

## Security notes

- Treat `config.json` as sensitive because it contains encrypted but live account/session-derived material.
- Treat `cookie.txt` as sensitive runtime state.
- Keep per-account deployment variants separate from the public showcase repo.

## Disclaimer

Shared for educational and automation-architecture reference. Use it responsibly and according to the target platform's rules and your own risk tolerance.
