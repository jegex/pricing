# Jegex Pricing

Standalone Laravel pricing engine — extracted from LunarPHP core into a universal, zero-dependency micro package.

## Requirements

- PHP ^8.3
- ext-intl
- Laravel ^12.0|^13.0

## Installation

```bash
composer require jegex/pricing
```

Laravel will auto-discover the `PricingServiceProvider` via package discovery.

If you want to customise the config:

```bash
php artisan vendor:publish --tag=pricing.config
```

To publish the migrations:

```bash
php artisan vendor:publish --tag=pricing.migrations
```

## Configuration

```php
// config/pricing.php

return [

    // Are prices stored inclusive of tax?
    'stored_inclusive_of_tax' => env('PRICING_STORE_INCLUSIVE_OF_TAX', false),

    // The price formatter class used by the Price value object.
    'formatter' => Jegex\Pricing\Pricing\DefaultPriceFormatter::class,

    // Pipeline classes that run after price matching.
    // Each receives the PricingManager instance and may modify $pricing.
    'pipelines' => [],

];
```

## Database

Two migrations are provided:

### `currencies`

| Column          | Type             | Notes                    |
|-----------------|------------------|--------------------------|
| id              | bigint (auto)    | PK                       |
| code            | string(10)       | UNIQUE                   |
| name            | string(255)      |                          |
| exchange_rate   | decimal(20,10)   |                          |
| decimal_places  | integer          | default 2                |
| enabled         | boolean          | default false            |
| default         | boolean          | default false            |
| sync_prices     | boolean          | default false            |
| created_at      | timestamp        |                          |
| updated_at      | timestamp        |                          |

### `prices`

| Column             | Type             | Notes                          |
|--------------------|------------------|--------------------------------|
| id                 | bigint (auto)    | PK                             |
| customer_group_id  | bigint           | FK, nullable                   |
| currency_id        | bigint           | FK -> currencies.id            |
| priceable_type     | string(255)      | nullable, morphs               |
| priceable_id       | bigint           | nullable, morphs               |
| price              | bigint           | stored as integer (cents)      |
| compare_price      | bigint           | nullable                       |
| min_quantity       | integer          | default 1                      |
| created_at         | timestamp        |                                |
| updated_at         | timestamp        |                                |

> Prices are stored as integers (e.g. 1000 = $10.00) to avoid floating-point precision issues.

## Models

### Currency (`Jegex\Pricing\Models\Currency`)

Table: `currencies`

**Traits**: `HasDefaultRecord`, `HasFactory`, `HasMacros`

**Scopes**:
- `enabled(bool $enabled = true)` — filter by enabled status
- `default(bool $default = true)` — filter by default flag

**Accessors**:
- `$currency->factor` — returns the factor string for decimal conversion (e.g. `100` for 2 decimal places)

**Methods**:
- `Currency::getDefault()` — returns the default currency, cached in-request
- `$currency->prices()` — hasMany relationship to `Price`

**Casts**: `enabled` (boolean), `default` (boolean), `sync_prices` (boolean), `decimal_places` (integer)

### Price (`Jegex\Pricing\Models\Price`)

Table: `prices`

**Traits**: `HasFactory`, `HasMacros`

**Relationships**:
- `$price->priceable()` — morphTo (attach prices to any model)
- `$price->currency()` — belongsTo Currency

**Casts**:
- `price` → `Jegex\Pricing\Casts\Price` (converts to `PriceDataType` on get)
- `compare_price` → `Jegex\Pricing\Casts\Price` (same cast)

## Price Value Object (`Jegex\Pricing\DataTypes\Price`)

A value object representing a monetary price with its currency context.

```php
use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Models\Currency;

$currency = Currency::getDefault();
$price = new PriceDataType(1000, $currency, unitQty: 1);
```

**Properties**:
- `$price->value` — the integer value (cents)
- `$price->currency` — the Currency model
- `$price->unitQty` — unit quantity divisor

**Methods**:
- `$price->decimal(bool $rounding = true): float` — converts to decimal (e.g. `10.00`)
- `$price->unitDecimal(bool $rounding = true): float` — per-unit decimal
- `$price->formatted(?string $locale = null, ...): string` — locale-formatted (e.g. `$10.00`)
- `$price->unitFormatted(?string $locale = null, ...): string` — per-unit formatted
- `(string) $price` — returns the raw integer value

## Usage

### 1. Setup Currencies

```php
use Jegex\Pricing\Models\Currency;

Currency::create([
    'code' => 'USD',
    'name' => 'US Dollar',
    'exchange_rate' => 1.0000,
    'decimal_places' => 2,
    'enabled' => true,
    'default' => true,
]);

Currency::create([
    'code' => 'EUR',
    'name' => 'Euro',
    'exchange_rate' => 0.8500,
    'decimal_places' => 2,
    'enabled' => true,
    'default' => false,
]);
```

### 2. Make a Model Purchasable

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Jegex\Pricing\Contracts\Purchasable;
use Jegex\Pricing\Models\Price;

class Product extends Model implements Purchasable
{
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function getUnitQuantity(): int
    {
        return $this->unit_quantity ?: 1;
    }
}
```

### 3. Create Prices

```php
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

$product = Product::find(1);
$usd = Currency::where('code', 'USD')->first();

// Base price
$product->prices()->create([
    'currency_id' => $usd->id,
    'price' => 1999, // $19.99
    'compare_price' => 2499, // $24.99 (original/RRP)
    'min_quantity' => 1,
]);

// Customer-group price
$product->prices()->create([
    'currency_id' => $usd->id,
    'price' => 1799,
    'customer_group_id' => 1, // wholesale
    'min_quantity' => 1,
]);

// Price break
$product->prices()->create([
    'currency_id' => $usd->id,
    'price' => 1499,
    'min_quantity' => 10, // 10+ units
]);
```

### 4. Resolve Best Price

```php
use Jegex\Pricing\Facades\Pricing;

// Simple lookup
$response = Pricing::for($product)->get();

$response->matched->price->value;   // 1999
$response->matched->price->decimal(); // 19.99

// With customer group
$response = Pricing::for($product)
    ->customerGroup((object) ['id' => 1])
    ->get();

$response->matched->price->value; // 1799

// With quantity break
$response = Pricing::for($product)
    ->qty(10)
    ->get();

$response->matched->price->value; // 1499

// In a specific currency
$eur = Currency::where('code', 'EUR')->first();
$response = Pricing::for($product)
    ->currency($eur)
    ->get();
```

### 5. Format Prices

```php
$price = $response->matched->price;

$price->decimal();           // 19.99
$price->decimal(false);      // 19.99 (no rounding)
$price->unitDecimal();       // 19.99 (per unit)

$price->formatted();         // $19.99 (uses app locale)
$price->formatted('de_DE');  // 19,99 $
$price->unitFormatted();     // $19.99 / each

(string) $price;             // 1999
```

### 6. Working with PricingResponse

```php
$response = Pricing::for($product)->qty(15)->get();

$response->matched;                 // The winning price record
$response->base;                    // The base price (min_quantity = 1)
$response->priceBreaks;             // Collection of bulk prices
$response->customerGroupPrices;     // Collection of group-specific prices

$response->base->price->formatted();              // $19.99
$response->matched->price->formatted();            // $14.99
```

### 7. Price Cast — Automatic Conversion

When you access `$price->price` on a saved Price model, it automatically returns a `PriceDataType` value object:

```php
$price = Price::find(1);

// Get — returns PriceDataType
$dataType = $price->price;
$dataType->value;        // 1999
$dataType->decimal();    // 19.99

// Set — accepts raw integer or PriceDataType
$price->price = 2500;
$price->price = new PriceDataType(2500, Currency::getDefault());

// Null handling
$price->compare_price;   // null (if not set)
```

### 8. Pipeline Example

Create a pipeline to apply a discount:

```php
namespace App\Pricing\Pipelines;

use Closure;
use Jegex\Pricing\Managers\PricingManager;

class ApplyMemberDiscount
{
    public function handle(PricingManager $manager, Closure $next)
    {
        if ($manager->user && $manager->user->isMember()) {
            $matched = $manager->pricing->matched;
            $discounted = (int) round($matched->price->value * 0.9);
            $matched->price = $discounted;
        }

        return $next($manager);
    }
}
```

Register in `config/pricing.php`:

```php
'pipelines' => [
    App\Pricing\Pipelines\ApplyMemberDiscount::class,
],
```

### 9. Sync Prices Between Currencies

Enable sync on a currency:

```php
$eur = Currency::where('code', 'EUR')->first();
$eur->update(['sync_prices' => true]);
```

Now whenever a price on the **default** currency (USD) is created or updated, the `PriceObserver` automatically dispatches a job to create/update the corresponding EUR price using the exchange rate.

To manually trigger price creation for a new currency:

```php
use Jegex\Pricing\Jobs\CreateCurrencyPrices;

CreateCurrencyPrices::dispatch($eur);
```

## Pricing Manager (`Jegex\Pricing\Managers\PricingManager`)

Fluent interface to resolve the best price for a purchasable.

```php
use Jegex\Pricing\Facades\Pricing;
use Jegex\Pricing\Models\Currency;

$response = Pricing::for($product)
    ->currency(Currency::getDefault())
    ->qty(3)
    ->customerGroup($group)
    ->get();
```

### Chained Methods

| Method | Description |
|---|---|
| `for(Purchasable $purchasable)` | Set the purchasable |
| `currency(?Currency $currency)` | Set target currency (defaults to default currency) |
| `user(?Authenticatable $user)` | Set the authenticated user |
| `guest()` | Clear the user |
| `qty(int $qty)` | Set quantity for price-break matching |
| `customerGroups(?Collection $groups)` | Set customer groups |
| `customerGroup($group)` | Set a single customer group |
| `get()` | Resolve the price, returns `PricingResponse` |

### Price Resolution Logic

1. Filter prices by the target currency
2. Filter prices matching the customer groups
3. Sort by price ascending
4. Match: base price (`min_quantity = 1`, no customer group)
5. Override with customer-group-specific price if available
6. Override with price-break if `qty >= min_quantity`
7. Throws `MissingCurrencyPriceException` if no price exists for the currency
8. Throws `ErrorException` if no matching price is found

## PricingResponse (`Jegex\Pricing\DataTransferObjects\PricingResponse`)

```php
class PricingResponse {
    public Price $matched;         // The matched price
    public Price $base;             // The base price (min_quantity = 1)
    public Collection $priceBreaks; // Price-break records
    public Collection $customerGroupPrices; // Customer-group-specific prices
}
```

## Purchasable Contract

Implement `Jegex\Pricing\Contracts\Purchasable` on any model to make it priceable:

```php
use Illuminate\Support\Collection;
use Jegex\Pricing\Contracts\Purchasable;

class Product extends Model implements Purchasable
{
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function getUnitQuantity(): int
    {
        return $this->unit_quantity ?: 1;
    }
}
```

## Price Cast (`Jegex\Pricing\Casts\Price`)

Automatically casts `price` and `compare_price` columns to `PriceDataType` on get, and back to raw values on set.

```php
protected $casts = [
    'price' => \Jegex\Pricing\Casts\Price::class,
    'compare_price' => \Jegex\Pricing\Casts\Price::class,
];
```

When the database value is `null`, the cast returns `null`.

## Price Formatter

Customise price formatting by implementing `Jegex\Pricing\Pricing\PriceFormatterInterface`:

```php
interface PriceFormatterInterface
{
    public function decimal(): float;
    public function unitDecimal(): float;
    public function formatted(): mixed;
    public function unitFormatted(): mixed;
}
```

Override via `config/pricing.php`:

```php
'formatter' => App\Pricing\MyFormatter::class,
```

### DefaultPriceFormatter

Uses PHP's `NumberFormatter` (ext-intl) for locale-aware formatting. Supports:
- Decimal conversion via currency factor
- Locale-specific currency symbols
- Trailing zero trimming
- Custom decimal places

## Currency Sync

When a currency has `sync_prices = true`, the package can automatically sync prices between currencies using exchange rates.

### Observer

`PriceObserver` dispatches `SyncPriceCurrencies` when a price on the **default** currency is created or updated, provided other currencies have `sync_prices` enabled.

### Jobs

- `SyncPriceCurrencies` — syncs a price record to all synced currencies
- `CreateCurrencyPrices` — bulk-creates prices for a new currency based on the default currency

### Action

- `CreateCurrencyPrices` — the underlying action that uses `insertUsing` for bulk price creation

To disable migrations (e.g. you manage them yourself):

```php
// config/pricing.php
'database' => [
    'disable_migrations' => true,
],
```

## Exceptions

| Exception | Extends | Thrown |
|---|---|---|
| `PricingException` | `\Exception` | Base exception |
| `MissingCurrencyPriceException` | `PricingException` | No prices exist for the target currency |

## Helpers

```php
// Check if prices are stored inclusive of tax
prices_inc_tax(); // bool

// Check if foreign key drops are supported (not SQLite, or SQLite >= Laravel 11.15)
can_drop_foreign_keys(); // bool
```

## Extending with Macros

Models use the `HasMacros` trait, enabling runtime extension:

```php
use Jegex\Pricing\Models\Currency;

Currency::macro('toArray', function () {
    return [
        'code' => $this->code,
        'name' => $this->name,
    ];
});
```

## Pipelines

The PricingManager runs matched prices through configurable pipelines before returning. Add pipeline classes to `config('pricing.pipelines')`:

```php
// config/pricing.php
'pipelines' => [
    App\Pricing\Pipelines\ApplyTax::class,
],
```

Each pipeline receives the `PricingManager` instance and can modify `$pricingManager->pricing`.

## Running Tests

```bash
composer install
vendor/bin/phpunit
```
