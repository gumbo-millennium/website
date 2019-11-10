<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use Illuminate\Support\Facades\Cache;
use JsonSchema\Exception\JsonDecodingException;

/**
 * Provides a list of iDEAL-supported banks, from a file.
 */
trait ProvidesBankList
{
    /**
     * @var string[]
     */
    protected $cachedBankList;

    /**
     * Get the list of ideal banks, keyed by the internal name
     *
     * @return string[]
     */
    public function getBankList(): array
    {
        // Check for cached result
        if ($this->cachedBankList !== null) {
            return $this->cachedBankList;
        }

        if (Cache::has('ideal.banklist')) {
            return Cache::get('ideal.banklist');
        }

        // Cache empty array
        $this->cachedBankList = [];

        // Get path to resource
        $path = resource_path('assets/json/ideal-banks.json');
        if (!file_exists($path) || !is_readable($path)) {
            // Log error
            logger()->warning("iDEAL configuration file (bank list) missing", [
                'file' => $path
            ]);

            // Return empty list
            return $this->cachedBankList;
        }

        // Read resource to JSON
        try {
            // Read up to 1MB (the list of banks should be waaay less than that)
            $data = file_get_contents($path, false, null, 0, 1024 * 1024);

            // Convert to JSON, throwing errors if not possible
            $json = json_decode($data, true, 2, JSON_THROW_ON_ERROR);

            // Assign JSON to local cache
            $this->cachedBankList = $json;

            // And also cache result for 6 hrs
            Cache::put('ideal.banklist', $json, now()->addHours(6));
        } catch (JsonDecodingException $e) {
            // Log error
            logger()->warning("iDEAL configuration file (bank list) not readable", [
                'exception' => $e,
                'file' => $path
            ]);

            // Return empty array via finally
        } finally {
            // Return list, which is empty by default
            return $this->cachedBankList;
        }
    }
}
