# Changelog

## 1.0.0 — Unreleased

### Added
- Initial release: standalone Laravel pricing engine extracted from LunarPHP
- Currency model with `HasDefaultRecord` trait, scopes, and factory support
- Price model with polymorphic morphs, casts, and factory support
- `PriceDataType` value object for integer-based pricing (avoids float precision issues)
- `DefaultPriceFormatter` with `decimal()`, `unitDecimal()`, `formatted()`, `unitFormatted()` methods
- `PricingManager` with fluent API: `for()`, `user()`, `currency()`, `qty()`, `customerGroups()`, `customerGroup()`, `get()`
- Pipeline-based extensibility for custom pricing logic
- Price sync across currencies via `SyncPriceCurrencies` job (queueable, unique)
- Automatic price creation for new currencies via `CreateCurrencyPrices` job
- `PriceObserver` that dispatches sync jobs on create/update/delete
- `HasMacros` trait for runtime macro extension
- Helper functions: `prices_inc_tax()`, `can_drop_foreign_keys()`
- Custom exceptions: `PricingException`, `MissingCurrencyPriceException`
- Configuration for tax mode, formatter, pipelines, and migration control
- PHPStan level 6 compliance
- Pest test suite with 45+ tests

### Changed
- Price cast now uses `is_numeric()` instead of `Validator::make()->validate()` for lighter type checking
- `MissingCurrencyPriceException` accepts optional `$currencyCode` for context-rich error messages
- `code` column in `currencies` table changed from `string(3)` to `string(10)` for crypto support
- Removed unused `InvalidDataTypeValueException` (typed constructor parameters make it dead code)
- Removed empty `CustomerGroup` contract interface
- CI workflow now runs PHPStan at level 6 and caches Composer dependencies
