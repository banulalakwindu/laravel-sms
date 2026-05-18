# Agent guide: `banulakwin/laravel-sms`

Portable Laravel SMS package with multi-provider driver architecture.

## Dependencies

- **`textlk/textlk-php`** for TextLK integration.
- `illuminate/support`

## Install

- Auto-discovery registers `SmsServiceProvider`.

## Config

- Publish: `php artisan vendor:publish --tag=sms-config` → `config/sms.php`.

## Usage

- Resolves `SmsManager` singleton from container.
- Use global helper `sms()`.
- DTOs used for request (`SendSmsRequest`) and response (`SendSmsResponse`).
- Use `sms()->send()` or `sms()->schedule()` for message dispatching.

## Do

- Use `sms()->driver('textlk')` for explicit driver selection.
- Pass recipients as `94XXXXXXXXX` format (auto-normalized).
- Use `SendSmsResponse` DTO for checking success, cost, and status.

## Do not

- Import `App\*` from the package.
- Hardcode API keys — use config/env.
- Bypass the `SmsManager` — always use `sms()` or inject `SmsManager`.

## Testing & Quality

```bash
composer test          # PHPUnit
composer pint          # Laravel Pint code style fix
composer pint:check    # Pint check only (no fix)
composer phpstan       # PHPStan level max on src/
composer quality       # All: pint + phpstan + test
```

## CI

GitHub Actions runs tests, Pint, and PHPStan on push/PR (`.github/workflows/tests.yml`).
