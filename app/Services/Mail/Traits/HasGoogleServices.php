<?php

declare(strict_types=1);

namespace App\Services\Mail\Traits;

use Closure;
use Google_Client as GoogleApi;
use Google_Service_Directory as GoogleDirectoryService;
use Google_Service_Directory_Resource_Groups as GoogleDirectoryGroupsResource;
use Google_Service_Directory_Resource_GroupsAliases as GoogleDirectoryAliasesResource;
use Google_Service_Directory_Resource_Members as GoogleDirectoryMembersResource;
use Google_Service_Exception as GoogleServiceException;
use Google_Service_Groupssettings as GoogleGroupsSettingsService;
use Google_Service_Groupssettings_Resource_Groups as GoogleGroupsSettingsResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Provides shorthands for Google services
 */
trait HasGoogleServices
{
    private ?GoogleDirectoryService $googleDirectoryService = null;
    private ?GoogleGroupsSettingsService $googleGroupsSettingsService = null;

    /**
     * Returns a Google client
     * @return null|Google_Client
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function getGoogleClient(): ?GoogleApi
    {
        // Get from container
        return \app(GoogleApi::class);
    }

    /**
     * Safely calls a Google service, handling any errors as they come up
     * @param Closure $method
     * @param string $action For logging, action performed
     * @param string $subject For logging, subject performed on
     * @return null|mixed
     * @throws InvalidArgumentException
     */
    public function callGoogleService(Closure $method, string $action, string $subject)
    {
        try {
            // Log start
            Log::debug('[Google] Calling {action} for {subject}', compact('action', 'subject'));

            // Call
            $result = $method();

            // Log end
            Log::debug('[Google] Completed {action} for {subject} successfully', compact('action', 'subject'));

            // Return
            return $result;
        } catch (GoogleServiceException $exception) {
            // Handle not found
            if ($exception->getCode() === 404) {
                Log::debug('[Google] {action} for {subject} resulted in a 404', compact('action', 'subject'));
                return null;
            }

            // Log error
            Log::notice('[Google] {action} for {subject} failed with {exception}', compact('action', 'subject', 'exception'));

            // Construct a better error
            $firstErrorMsg = Arr::get(Arr::first($exception->getErrors()), 'message') ?? $exception->getMessage();
            $firstErrorCode = Arr::get(Arr::first($exception->getErrors()), 'code') ?? $exception->getCode();
            $message = "Failed to call {$action} on {$subject}: #{$firstErrorCode} {$firstErrorMsg}";

            // Wrap it
            $wrappedException = new RuntimeException($message, $firstErrorCode, $exception);

            // Report it
            \report($wrappedException);

            // Return null
            return null;
        }
    }

    /**
     * Returns a Google Directory Groups manager
     * @return GoogleDirectoryGroupsResource
     * @throws InvalidArgumentException
     * @internal
     */
    protected function getGoogleGroupManager(): GoogleDirectoryGroupsResource
    {
        return $this->getGoogleDirectory()->groups;
    }

    /**
     * Returns a Google Directory Group Alias manager
     * @return GoogleDirectoryAliasesResource
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupAliasManager(): GoogleDirectoryAliasesResource
    {
        return $this->getGoogleDirectory()->groups_aliases;
    }

    /**
     * Returns Google Directory Group Members manager
     * @return GoogleDirectoryMembersResource
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupMembersManager(): GoogleDirectoryMembersResource
    {
        return $this->getGoogleDirectory()->members;
    }

    /**
     * Returns Google Groups Settings manager
     * @return GoogleGroupsSettingsResource
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupSettingsManager(): GoogleGroupsSettingsResource
    {
        return $this->getGoogleGroupSettings()->groups;
    }

    /**
     * Returns Google directory service
     * @return GoogleDirectoryService
     * @throws InvalidArgumentException
     * @internal
     */
    private function getGoogleDirectory(): GoogleDirectoryService
    {
        // Create new if missing
        if (!$this->googleDirectoryService) {
            $this->googleDirectoryService = new GoogleDirectoryService($this->getGoogleClient());
        }

        // Create existing
        return $this->googleDirectoryService;
    }

    /**
     * Returns Google Groups Settings client
     * @return GoogleGroupsSettingsService
     * @throws InvalidArgumentException
     * @internal
     */
    private function getGoogleGroupSettings(): GoogleGroupsSettingsService
    {
        // Create new if missing
        if (!$this->googleGroupsSettingsService) {
            $this->googleGroupsSettingsService = new GoogleGroupsSettingsService($this->getGoogleClient());
        }

        // Create existing
        return $this->googleGroupsSettingsService;
    }
}
