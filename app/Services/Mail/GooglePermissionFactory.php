<?php

declare(strict_types=1);

namespace App\Services\Mail;

use Google_Service_Groupssettings_Groups as GroupSettings;
use InvalidArgumentException;
use JsonException;
use LogicException;

class GooglePermissionFactory
{
    public const CONFIG_DEFAULT = 'default';

    public const CONFIG_ACTIVITY = 'activity';

    public const REPLY_SENDER = 'REPLY_TO_SENDER';

    public const REPLY_CUSTOM = 'REPLY_TO_CUSTOM';

    public const REPLY_MANAGERS = 'REPLY_TO_MANAGERS';

    private const EMAIL_CONFIG_BASE = 'assets/json/mail';

    /**
     * Foundational config.
     */
    private array $baseConfig;

    /**
     * Configs added via `append()`.
     */
    private array $additionalConfigs = [];

    /**
     * User-level changes.
     */
    private array $userConfig = [];

    public static function make(?string $append = null)
    {
        // Sanity
        if ($append === self::CONFIG_DEFAULT) {
            throw new InvalidArgumentException('Cannot append the config that\'s applied by default');
        }

        // Load config
        $defaultConfig = self::getConfig(self::CONFIG_DEFAULT);

        // Create instance
        $instance = new self($defaultConfig);

        // Append if requested
        if ($append) {
            $instance->append($append);
        }

        // Done
        return $instance;
    }

    /**
     * Handles fetching a config in a safe manner.
     * Validates config name, config existence, config data and returns an array on success.
     *
     * @param string $configName Config name
     * @return array<int|string> Settings
     * @throws InvalidArgumentException
     */
    protected static function getConfig(string $configName): array
    {
        // Validate name
        if (! \preg_match('/^[a-z-]{2,30}$/', $configName)) {
            throw new InvalidArgumentException('Config name is invalid');
        }

        // Get filename
        $fileName = "permissions-{$configName}.json";
        if ($configName === self::CONFIG_DEFAULT) {
            $fileName = 'permissions.json';
        }

        // Get full path
        $configPath = \resource_path(self::EMAIL_CONFIG_BASE . \DIRECTORY_SEPARATOR . $fileName);

        // Throw a fit if file is missing
        if (! \file_exists($configPath) || ! \is_file($configPath)) {
            throw new InvalidArgumentException("Config named [{$configName}] does not exist at [{$configPath}]");
        }

        // Get contents
        $contents = \file_get_contents($configPath, false, null, 0, 8192);

        try {
            // Decode contents on receive
            return \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException("Config named [{$configName}] is invalid", 0, $exception);
        }
    }

    protected function __construct(array $config)
    {
        $this->baseConfig = $config;
    }

    /**
     * Adds a set of configs.
     *
     * @return exit
     * @throws InvalidArgumentException
     */
    public function append(string $configName): self
    {
        // Add item
        $this->additionalConfigs[] = self::getConfig($configName);

        // Return instance
        return $this;
    }

    /**
     * Appends raw settings.
     *
     * @throws InvalidArgumentException
     */
    public function appendRaw(array $options): self
    {
        // Validate each key
        foreach ($options as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException("Config key [{$key}] is invalid");
            }
            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException("Config value on [{$key}] is invalid");
            }
        }

        // Add config as copy
        $this->additionalConfigs[] = array_merge([], $options);

        // Return instance
        return $this;
    }

    /**
     * Sets how users reply to this list.
     *
     * @return void
     */
    public function setReplyPolicy(string $policy, ?string $replyTo = null): self
    {
        // Only allow valid options
        if (
            ! in_array($policy, [
                self::REPLY_SENDER,
                self::REPLY_CUSTOM,
                self::REPLY_MANAGERS,
            ], true)
        ) {
            throw new InvalidArgumentException('Invalid reply policy');
        }

        // Validate reply-to
        if (empty($replyTo) || filter_var($replyTo, \FILTER_VALIDATE_EMAIL) === false) {
            $replyTo = '';
        }

        // Require email on custom
        if ($policy === self::REPLY_CUSTOM && ! $replyTo) {
            throw new InvalidArgumentException('A custom, valid reply-to address is required with REPLY_CUSTOM');
        }

        // Forbid email on non-custom
        if ($policy !== self::REPLY_CUSTOM && $replyTo) {
            throw new InvalidArgumentException('A custom reply-to address can only be set with REPLY_CUSTOM');
        }

        // Save
        $this->userConfig['replyTo'] = $policy;
        $this->userConfig['customReplyTo'] = $replyTo;

        // Chain
        return $this;
    }

    /**
     * Sets footer on all inbound mail.
     */
    public function setFooter(?string $footer): self
    {
        // Clean up
        $footer = trim($footer);

        // Save
        $this->userConfig['includeCustomFooter'] = ! empty($footer);
        $this->userConfig['customFooterText'] = ! empty($footer) ? $footer : '';

        // Chain
        return $this;
    }

    /**
     * Sets message sent when an e-mail is bounced.
     */
    public function setDenyReply(?string $reply): self
    {
        // Clean up
        $reply = trim($reply);

        // Save
        $this->userConfig['sendMessageDenyNotification'] = ! empty($reply);
        $this->userConfig['defaultMessageDenyNotificationText'] = ! empty($reply) ? $reply : '';

        // Chain
        return $this;
    }

    /**
     * Builds settings as Google object.
     *
     * @throws LogicException
     */
    public function build(): GroupSettings
    {
        return new GroupSettings($this->toArray());
    }

    /**
     * Constructs the settings.
     */
    public function toArray(): array
    {
        $config = array_merge([], $this->baseConfig, $this->userConfig);
        $cleanConfig = [];

        foreach ($config as $key => $value) {
            // Re-check keys
            if (! \is_string($key)) {
                throw new LogicException("Key [{$key}] is not a string, filtering is failing.");
            }

            // Re-check values
            if (! \is_scalar($value) && null !== $value) {
                throw new LogicException("Value at [{$key}] is not scalar or null, filtering is failing.");
            }

            // Format value
            if (null === $value) {
                $value = '';
            } elseif (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            // Assign value
            $cleanConfig[$key] = $value;
        }

        // Return config
        return $cleanConfig;
    }
}
