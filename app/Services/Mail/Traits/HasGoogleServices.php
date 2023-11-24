<?php

declare(strict_types=1);

namespace App\Services\Mail\Traits;

use Closure;
use Google\Client as GoogleClient;
use Google\Service\Directory as DirectoryService;
use Google\Service\Directory\Resource\Groups as DirectoryGroups;
use Google\Service\Directory\Resource\GroupsAliases as DirectoryAliases;
use Google\Service\Directory\Resource\Members as DirectoryMembers;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Groupssettings as GroupsSettingsService;
use Google\Service\Groupssettings\Resource\Groups as GroupsSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Provides shorthands for Google services.
 */
trait HasGoogleServices
{
    private ?DirectoryService $googleDirectoryService = null;

    private ?GroupsSettingsService $googleGroupsSettingsService = null;

    /**
     * Safely calls a Google service, handling any errors as they come up.
     *
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
            Log::notice(
                '[Google] {action} for {subject} failed with {exception}',
                compact('action', 'subject', 'exception'),
            );

            // Construct a better error
            $firstErrorMsg = Arr::get(Arr::first($exception->getErrors()), 'message') ?? $exception->getMessage();
            $firstErrorCode = Arr::get(Arr::first($exception->getErrors()), 'code') ?? $exception->getCode();
            $message = "Failed to call {$action} on {$subject}: #{$firstErrorCode} {$firstErrorMsg}";

            // Wrap it
            $wrappedException = new RuntimeException($message, $firstErrorCode, $exception);

            // Report it
            throw $wrappedException;
        }
    }

    /**
     * Returns a Google Directory Groups manager.
     *
     * @throws InvalidArgumentException
     * @internal
     */
    protected function getGoogleGroupManager(): DirectoryGroups
    {
        return $this->getGoogleDirectory()->groups;
    }

    /**
     * Returns a Google Directory Group Alias manager.
     *
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupAliasManager(): DirectoryAliases
    {
        return $this->getGoogleDirectory()->groups_aliases;
    }

    /**
     * Returns Google Directory Group Members manager.
     *
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupMembersManager(): DirectoryMembers
    {
        return $this->getGoogleDirectory()->members;
    }

    /**
     * Returns Google Groups Settings manager.
     *
     * @throws InvalidArgumentException
     */
    protected function getGoogleGroupSettingsManager(): GroupsSettings
    {
        return $this->getGoogleGroupSettings()->groups;
    }

    /**
     * Returns a Google client.
     *
     * @return null|Google_Client
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function getGoogleClient(): ?GoogleClient
    {
        // Get from container
        return \app(GoogleClient::class);
    }

    /**
     * Returns Google directory service.
     *
     * @throws InvalidArgumentException
     * @internal
     */
    private function getGoogleDirectory(): DirectoryService
    {
        // Create new if missing
        if (! $this->googleDirectoryService) {
            $this->googleDirectoryService = new DirectoryService($this->getGoogleClient());
        }

        // Create existing
        return $this->googleDirectoryService;
    }

    /**
     * Returns Google Groups Settings client.
     *
     * @throws InvalidArgumentException
     * @internal
     */
    private function getGoogleGroupSettings(): GroupsSettingsService
    {
        // Create new if missing
        if (! $this->googleGroupsSettingsService) {
            $this->googleGroupsSettingsService = new GroupsSettingsService($this->getGoogleClient());
        }

        // Create existing
        return $this->googleGroupsSettingsService;
    }
}
