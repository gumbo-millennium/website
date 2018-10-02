<?php
declare (strict_types = 1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Handles indicating and changing the privacy settings of the request.
 *
 * @author Roelof Roos
 * @license MPL-2.0
 */
class PrivacyProvider
{
    /**
     * Key containing the properties for this request
     */
    const SESSION_KEY = 'gdpr_privacy';

    /**
     * Key containing the ID of this kind of request
     */
    const SESSION_ID_KEY = 'gdpr_key';

    /**
     * Indicates the system has no clue if the user wants to be tracked or not
     */
    const TRACK_NONE = 0;

    /**
     * User allowed Stripe fraud prevention
     */
    const TRACK_STRIPE = 2;

    /**
     * User granted all tracking.
     */
    const TRACK_ALL = self::TRACK_STRIPE;

    /**
     * Build the session
     */
    public function __construct()
    {
        // Create tracking ID, used for requests to /.well-known/dnt
        if (!session()->has(self::SESSION_ID_KEY)) {
            session()->put(self::SESSION_ID_KEY, Str::random());
        }

        // Create default tracking preference
        if (!session()->has(self::SESSION_KEY)) {
            $this->determineDefaultTrackingStatus();
        }
    }

    protected function determineDefaultTrackingStatus() : void
    {
        // Get DNT value
        $request = request();
        $dnt = null;
        if ($request->headers->has('DNT')) {
            $dnt = (string)$request->headers->get('DNT');
        }

        // Check DNT value
        if ($dnt === "1") {
            // Allow all tracking.
            $this->setTrackingStatus(self::TRACK_ALL);
        } elseif ($dnt === "0") {
            // Allow no tracking, if DNT = 0.
            $this->setTrackingStatus(self::TRACK_NONE);
        } else {
            // Allow basic, 1st party tracking if not specified
            $this->setTrackingStatus(self::TRACK_NONE);
        }
    }

    /**
     * Returns current tracking status
     *
     * @return int
     */
    public function getTrackingStatus() : int
    {
        return $request->session()->get(self::SESSION_KEY);
    }

    /**
     * Sets the tracking status, uses bitwise comparison to makes sure the value is valid.
     *
     * @param int $status
     * @return void
     */
    public function setTrackingStatus(int $status) : void
    {
        session()->put(
            self::SESSION_KEY,
            $status & self::TRACK_ALL
        );
    }

    /**
     * Returns tracking status value as specified in https://www.w3.org/TR/tracking-dnt/#tracking-status-value
     *
     * @return string
     */
    public function getTrackingStatusValue() : string
    {
        $value = $this->getTrackingStatus();
        if ($value === self::TRACK_NONE) {
            // Not tracking
            return 'N';
        } elseif ($value & self::TRACK_STRIPE) {
            // Stripe cookie is only set after consent
            return 'C';
        } else {
            // Just say tracking, as we don't really know now
            return '!';
        }
    }

    /**
     * Allow to call `allow[Type]Tracking` methods, for easy access to variables
     *
     * @param string $method
     * @param array $args
     * @return bool
     * @throws \BadMethodCallException if the method or tracking type requested does not exist.
     */
    public function __call(string $method, array $args) : bool
    {
        if (preg_match('/^allow([A-Z][a-z]+)Tracking$/', $method, $matches)) {
            $constant = sprintf('TRACK_%s', strtoupper($matches[1]));
            if (defined("static::$constant")) {
                $wantedValue = static::$constant;
                $currentValue = $this->getTrackingStatus();
                if ($wantedValue === 0) {
                    return $currentValue === 0;
                } else {
                    return $currentValue & $wantedValue == $wantedValue;
                }
            }
        }

        throw new \BadMethodCallException("The method [{$method}] does not exist.");
    }
}
