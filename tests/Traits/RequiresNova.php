<?php

declare(strict_types=1);

namespace Tests\Traits;

use Laravel\Nova\Nova;

trait RequiresNova
{
    /**
     * Skip the test if Nova is only available as dummy.
     *
     * @before
     * @return void
     */
    public function ensureNovaIsAvailableBeforehand(): void
    {
        if (is_string(Nova::version())) {
            return;
        }

        $this->markTestSkipped('Nova only available as dummy, skipping dummy test.');
    }
}
