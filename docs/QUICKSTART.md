# QUICKSTART

## What you need

- Python 3.10+ for `multiAcc.py`
- or PHP 8.1+ for `multiAcc.php`
- a local `config.json` copied from `config.example.json`

## Python flow

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp config.example.json config.json
python3 multiAcc.py
```

## PHP flow

```bash
cp config.example.json config.json
php multiAcc.php
```

## Before you run

1. Replace placeholder account/app values inside `config.json`
2. Keep any real session-derived files local only
3. Do not commit runtime cookies, encrypted account blobs, or private deployment bundles
