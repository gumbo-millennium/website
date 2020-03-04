<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use JsonException;

/**
 * Provides banks for iDEAL
 * @author Roelof Roos <github@roelof.io>
 */
class IdealBankService
{
    /**
     * Path to the bank list
     */
    private const BANKS_FILE = 'assets/json/ideal-banks.json';

    /**
     * @var Collection
     * @package App\Services
     */
    private $cachedList = null;

    /**
     * Returns all banks
     * @return array
     */
    public function getAll(): array
    {
        return $this->getList()->all();
    }

    /**
     * Returns all bank names
     * @return array
     */
    public function names(): array
    {
        return $this->getList()->values()->all();
    }

    /**
     * Returns all Stripe bank codes
     * @return array
     */
    public function codes(): array
    {
        return $this->getList()->keys()->all();
    }

    /**
     * Returns the name of the bank with the given code
     * @param string $code
     * @return null|string
     */
    public function getName(string $code): ?string
    {
        return $this->getList()->get($code);
    }

    /**
     * Returns the bank list
     * @return Illuminate\Support\Collection
     */
    private function getList(): Collection
    {
        // Check cached
        if ($this->cachedList) {
            return $this->cachedList;
        }

        // Check global cache
        if (Cache::has('ideal.banklist')) {
            $this->cachedList = Collection::make(Cache::get('ideal.banklist'));
            return $this->cachedList;
        }

        // Cache nothing
        $this->cachedList = Collection::make();

        // Check existance
        $path = \resource_path(self::BANKS_FILE);
        if (!\file_exists($path)) {
            return $this->cachedList;
        }

        // Convert from json
        try {
            $json = json_decode(\file_get_contents($path), true, 15, \JSON_THROW_ON_ERROR);
            Cache::put('ideal.banklist', $json, now()->addHours(6));
            return $this->cachedList = Collection::make($json);
        } catch (JsonException $e) {
            return $this->cachedList;
        }
    }
}
