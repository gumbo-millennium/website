<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use Google\Model as GoogleModel;

trait DeepComparesArraysAndObjects
{
    /**
     * Deep compares two arrays or objects, returns an array of differences,
     * merged with the original data.
     * @param null|string $forceCastTo force the result to cast to a specific type, if changed
     */
    protected function deepCompareArrayToObject(array $expected, array|GoogleModel $actual, ?string $forceCastTo = null): null|array|GoogleModel
    {
        $changes = [];

        foreach ($expected as $key => $expectedValue) {
            $actualValue = $actual instanceof GoogleModel ? $actual->{$key} : ($actual[$key] ?? null);

            // Handle existing null values
            if ($actualValue === null && $expectedValue !== null) {
                $changes[$key] = $expectedValue;

                continue;
            }

            // Handle new null values
            if ($expectedValue === null && $actualValue !== null) {
                $changes[$key] = null;

                continue;
            }

            // Handle scalar comparison
            if (! is_array($expectedValue) && $actualValue != $expectedValue) {
                $changes[$key] = $expectedValue;

                continue;
            }

            // Handle deep array comparison
            if (is_array($expectedValue) && (is_array($actualValue) || $actualValue instanceof GoogleModel)) {
                $deeperChanges = $this->deepCompareArrayToObject($expectedValue, $actualValue);

                // Save if any changes were made
                if ($deeperChanges) {
                    $changes[$key] = $deeperChanges;
                }

                continue;
            }
        }

        if (empty($changes)) {
            return null;
        }

        $castTo = $forceCastTo ?? ($actual instanceof GoogleModel ? get_class($actual) : null);
        if (! $castTo) {
            return $changes;
        }

        throw_unless(is_a($castTo, GoogleModel::class, true), LogicException::class, 'The cast to type must be a Google Model.');

        $newInstance = new $castTo();
        foreach ($changes as $key => $value) {
            $newInstance->{$key} = $value;
        }

        return $newInstance;
    }
}
