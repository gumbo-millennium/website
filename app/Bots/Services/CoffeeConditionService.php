<?php

declare(strict_types=1);

namespace App\Bots\Services;

use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CoffeeConditionService
{
    private const COFFEE_FILE = 'bot/coffee.json';

    private const COFFEE_FRESHNESS = [
        'PT15M' => 'fresh',
        'PT1H' => 'okay',
        'PT2H' => 'stale',
        'PT4H' => 'old',
        'PT8H' => 'yeasty',
    ];

    /**
     * Builds a report on the coffee condition, returning the chat message to send.
     */
    public function getCoffeeCondition(): string
    {
        $status = $this->readCoffeeStatus();
        if ($status === null) {
            return __('The coffee condition is unknown.');
        }

        /** @var Carbon $date */
        $date = $status->date;
        if ($date->dayOfYear() != Date::now()->dayOfYear()) {
            return __('The coffee condition is unknown.');
        }

        // If it's gone, it's gone
        if (! $status->coffee) {
            return implode(PHP_EOL, [
                __('There is no more coffee.'),
                __('The state was last updated by :user :ago.', [
                    'user' => $status->user->first_name,
                    'ago' => $date->diffForHumans(),
                ]),
            ]);
        }

        $condition = 'very fresh';
        foreach (self::COFFEE_FRESHNESS as $interval => $freshness) {
            if (Date::now()->sub($interval)->isAfter($date)) {
                $condition = $freshness;
            }
        }

        return implode(PHP_EOL, [
            __('The coffee is :condition.', ['condition' => __($condition)]),
            __('The coffee was brewed by :user :ago.', [
                'user' => $status->user->first_name,
                'ago' => $date->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Changes the coffee condition.
     */
    public function setCoffee(User $user, bool $coffee): string
    {
        $this->writeCoffeeStatus($user, $coffee);

        return __($coffee ? 'Thank you for brewing coffee :name!' : 'Thank you for letting us know there is no more coffee :name!', [
            'name' => $user->first_name,
        ]);
    }

    /**
     * Returns coffee status, if any.
     */
    private function readCoffeeStatus(): ?object
    {
        try {
            $raw = Storage::get(self::COFFEE_FILE);
            $data = json_decode($raw, true, 4, JSON_THROW_ON_ERROR);

            $data = array_merge([
                'coffee' => null,
                'user' => null,
                'date' => null,
            ], json_decode($raw, true, 4, JSON_THROW_ON_ERROR));

            $data['user'] = User::find($data['user'] ?? -1);
            $data['date'] = Date::parse($data['date'] ?? 'now');

            return (object) $data;
        } catch (FileNotFoundException) {
            return null;
        }
    }

    /**
     * Writes the coffee status.
     * @throws InvalidArgumentException
     */
    private function writeCoffeeStatus(User $user, bool $isCoffee): void
    {
        Storage::put(self::COFFEE_FILE, json_encode([
            'coffee' => $isCoffee,
            'user' => $user->id,
            'date' => Date::now(),
        ]));
    }
}
