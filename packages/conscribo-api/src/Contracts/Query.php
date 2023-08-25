<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Contracts;

use Illuminate\Support\Collection;

interface Query
{
    /**
     * Runs the query
     */
    public function get(): Collection;
}
