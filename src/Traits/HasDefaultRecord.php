<?php

namespace Jegex\Pricing\Traits;

trait HasDefaultRecord
{
    /** @var array<class-string, \Illuminate\Database\Eloquent\Model|null> */
    private static array $defaultRecordCache = [];

    /**
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeDefault(\Illuminate\Database\Eloquent\Builder $query, bool $default = true): \Illuminate\Database\Eloquent\Builder
    {
        $query->where('default', $default);

        return $query;
    }

    public static function getDefault(): ?\Illuminate\Database\Eloquent\Model
    {
        $class = static::class;

        if (! array_key_exists($class, self::$defaultRecordCache)) {
            self::$defaultRecordCache[$class] = static::query()->default(true)->first();
        }

        return self::$defaultRecordCache[$class];
    }

    public static function forgetDefaultCache(): void
    {
        $class = static::class;
        unset(self::$defaultRecordCache[$class]);
    }
}
