<?php

declare(strict_types=1);

namespace Tests\Gumbo\ConscriboApi\fixtures;

use Gumbo\ConscriboApi\Concerns\HasConscriboWhereClauses;

class MockWithConscriboWhereClauses
{
    use HasConscriboWhereClauses;

    public function where(string $key, $operator, $value = null): self
    {
        return $this;
    }
}
