# CONFIG FORMAT

The public repo ships with `config.example.json` instead of a live `config.json` because the original private setup mixed code with encrypted account blobs and runtime state.

## Top-level keys

## `accounts`
An array of account objects.

Each account currently uses fields such as:
- `datalogin`
- `foreground`

These values come from the mobile/app workflow and are intentionally left as placeholders in the public example file.

## `apks`
An array describing the target app metadata used by the automation scripts.

Typical fields:
- `url`
- `packageName`
- `version`

## Why this matters

Both the Python and PHP variants rely on the same shape:

1. load account metadata from config
2. decrypt or process the stored values
3. build app/device headers
4. run per-account reward logic in parallel

That shared config structure is the real public teaching value of the repo, while the live encrypted values themselves should remain private.
