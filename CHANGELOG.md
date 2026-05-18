# Changelog

All notable changes to `banulakwin/laravel-sms` will be documented in this file.

## 1.0.0 — 2026-05-17

### Added
- Multi-provider SMS driver architecture with `SmsManager`.
- TextLK SMS provider implementation (`TextLkSmsProvider`).
- `SendSmsRequest` and `SendSmsResponse` DTOs.
- `SmsProvider` and `SmsStatus` enums.
- `SmsException` for error handling.
- Sri Lankan phone number normalization (94XXXXXXXXX format).
- Scheduled SMS support via `schedule()` method.
- `sms()` global helper function.
- Configurable provider registration via `config/sms.php`.
- PHPUnit test suite with Orchestra Testbench.
- GitHub Actions CI workflow (tests, Pint, PHPStan).
- Laravel Pint code style configuration.
- PHPStan static analysis (level max).
