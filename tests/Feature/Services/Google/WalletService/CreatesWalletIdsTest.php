<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google\WalletService;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Services\Google\Traits\CreatesWalletIds;
use App\Services\Google\WalletService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\TestCase;

class CreatesWalletIdsTest extends TestCase
{
    use CreatesWalletIds;

    public function test_failure_with_missing_issuer_id(): void
    {
        Config::set('services.google.wallet.issuer_id', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Google Wallet issuer ID not configured');

        $this->initializeCreatesWalletIds();
    }

    /**
     * A basic feature test example.
     */
    public function test_id_creation(): void
    {
        Config::set('services.google.wallet.issuer_id', '240000044');

        $this->initializeCreatesWalletIds();

        $activity = Activity::make()->forceFill([
            'id' => 39,
        ]);

        $enrollment = Enrollment::make()->forceFill([
            'id' => 933,
        ]);
        $enrollment->activity()->associate($activity);

        $this->assertSame('240000044', $this->getIssuerId());
        $this->assertSame('240000044.testing_A0039', $this->getActivityClassId($activity));
        $this->assertSame('240000044.testing_A0039_00933', $this->getEnrollmentObjectId($enrollment));

        $service = App::make(WalletService::class);
        $this->assertInstanceOf(WalletService::class, $service);

        $this->assertSame($this->getIssuerId(), $service->getIssuerId());
        $this->assertSame($this->getActivityClassId($activity), $service->getActivityClassId($activity));
        $this->assertSame($this->getEnrollmentObjectId($enrollment), $service->getEnrollmentObjectId($enrollment));
    }
}
