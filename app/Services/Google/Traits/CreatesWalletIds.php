<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use RuntimeException;

trait CreatesWalletIds
{
    private string $issuerId;

    /**
     * Returns the Issuer ID, should be fairly consistent through application
     * running.
     */
    public function getIssuerId(): string
    {
        if (! $this->issuerId) {
            $this->issuerId = Config::get('services.google.wallet.issuer_id');
            throw_unless($this->issuerId, new RuntimeException('Google Wallet issuer ID not configured'));
        }

        return $this->issuerId;
    }

    /**
     * Returns the class ID of the activity, derrived using the issuer ID and activity ID.
     */
    public function getActivityClassId(Activity $activity): string
    {
        return sprintf('%s.%s_A%04d', $this->getIssuerId(), Str::slug(App::environment()), $activity->id);
    }

    /**
     * Returns the Enrollment Google Wallet Object ID, using the activity class ID.
     */
    public function getEnrollmentObjectId(Enrollment $enrollment): string
    {
        return sprintf('%s_%05d', $this->getActivityClassId($enrollment->activity), $enrollment->id);
    }
}
