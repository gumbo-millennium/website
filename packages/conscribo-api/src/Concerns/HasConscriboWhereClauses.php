<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Concerns;

use DateTimeInterface;

trait HasConscriboWhereClauses
{
    abstract public function where(string $key, $operator, $value = null): self;

    /**
     * Check if the given string, email or account $key contains the given value, where spaces are treated as wildcards.
     *
     * @throws InvalidArgumentException
     */
    public function whereContains(string $key, $value): self
    {
        return $this->where($key, '~', $value);
    }

    /**
     * Check if the given string, email or account $key does not contain the given value, where spaces are treated as wildcards.
     *
     * @throws InvalidArgumentException
     */
    public function whereNotContains(string $key, $value): self
    {
        return $this->where($key, '!~', $value);
    }

    /**
     * Check if the given string, email or account $key starts with the given value.
     *
     * @throws InvalidArgumentException
     */
    public function whereStartsWith(string $key, $value): self
    {
        return $this->where($key, '|=', $value);
    }

    /**
     * Check if the given string, email or account $key is not empty.
     *
     * @throws InvalidArgumentException
     */
    public function whereNotEmpty(string $key): self
    {
        return $this->where($key, '+', '');
    }

    /**
     * Check if the given string, email or account $key is empty.
     *
     * @throws InvalidArgumentException
     */
    public function whereEmpty(string $key): self
    {
        return $this->where($key, '-', '');
    }

    /**
     * Check if the date is between $start and $stop.
     *
     * @throws InvalidArgumentException
     */
    public function whereDateIsBetween(string $key, DateTimeInterface $start, DateTimeInterface $stop): self
    {
        return $this->where($key, '><', ['start' => $start, 'stop' => $stop]);
    }

    /**
     * Check if the date is after $start.
     *
     * @throws InvalidArgumentException
     */
    public function whereDateIsAfter(string $key, DateTimeInterface $start): self
    {
        return $this->where($key, '>=', ['start' => $start]);
    }

    /**
     * Check if the date is before $stop.
     *
     * @throws InvalidArgumentException
     */
    public function whereDateIsBefore(string $key, DateTimeInterface $stop): self
    {
        return $this->where($key, '<=', ['stop' => $stop]);
    }

    /**
     * Check if the given number or amount $key is between $start and $stop.
     *
     * @throws InvalidArgumentException
     */
    public function whereNumberIsBetween(string $key, float|int $start, float|int $stop): self
    {
        return $this->where($key, '=', ">{$start}&<{$stop}");
    }

    /**
     * Check if the given number or amount $key is greater than $min.
     *
     * @throws InvalidArgumentException
     */
    public function whereNumberIsGreaterThan(string $key, float|int $min): self
    {
        return $this->where($key, '=', ">{$min}");
    }

    /**
     * Check if the given number or amount $key is less than $max.
     *
     * @throws InvalidArgumentException
     */
    public function whereNumberIsLessThan(string $key, float|int $max): self
    {
        return $this->where($key, '=', "<{$max}");
    }

    /**
     * Check if the given multicheckbox has at least one of the specified values.
     *
     * @throws InvalidArgumentException
     */
    public function whereMulticheckboxIn(string $key, int $value): self
    {
        return $this->where($key, 'in', $value);
    }

    /**
     * Check if the given multicheckbox has all of the specified values.
     *
     * @throws InvalidArgumentException
     */
    public function whereMulticheckboxAllIn(string $key, int $value): self
    {
        return $this->where($key, 'all', $value);
    }

    /**
     * Check if the given multicheckbox does NOT have any of the specified values.
     *
     * @throws InvalidArgumentException
     */
    public function whereMulticheckboxNotIn(string $key, int $value): self
    {
        return $this->where($key, '<>', $value);
    }
}
