# givvy

![Python](https://img.shields.io/badge/Python-Automation-3776AB?logo=python&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-Variant-777BB4?logo=php&logoColor=white)
![Target](https://img.shields.io/badge/Target-Givvy%20Reward%20Flow-111827)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

Automation scripts for a Givvy-style scratch-card reward workflow, preserved as a safe public showcase from a private working setup.

This public candidate intentionally keeps only the reusable code variants while excluding live encrypted account blobs, cookie jars, and duplicate deployment folders.

## Highlights

- Python and PHP implementations of the same workflow idea
- local config-driven account and APK metadata model
- encrypted mobile-state handling inside the automation flow
- public template packaging without live cookies or runtime blobs

## Included files

- `multiAcc.py` - Python implementation
- `multiAcc.php` - PHP implementation
- `config.example.json` - safe example config shape
- `requirements.txt`
- `LICENSE`

## Local-only files

The public repo intentionally excludes:

- real `config.json`
- real `cookie.txt`
- `cf.json`, `cf2.json`, `cf3.json`
- `reff.zip`
- the duplicate deployment folders from the original private layout

## Quick start

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

## Config structure

`config.json` contains two main arrays:

- `accounts`
  - encrypted `datalogin`
  - encrypted `foreground`
- `apks`
  - app `url`
  - `packageName`
  - `version`

Use `config.example.json` as the template only.

## Documentation

- `docs/CONFIG_FORMAT.md`

## Security notes

- Treat `config.json` as sensitive because it contains encrypted but still live account/session-derived material.
- Treat `cookie.txt` as sensitive runtime state.
- Keep per-account deployment variants separate from the public showcase repo.

## Disclaimer

Shared for educational and automation-architecture reference. Use it responsibly and according to the target platform's rules and your own risk tolerance.
