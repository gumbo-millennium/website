<?php

declare(strict_types=1);

namespace App\Services\Mail\Traits;

use App\Helpers\Str;
use Illuminate\Support\Facades\Config;

/**
 * Validates email mutations and email lists
 */
trait ValidatesEmailRequests
{
    /**
     * Valid committee handles
     * @var array<string>
     */
    private array $validEmails = [
        'commissie' => '/^(?:[a-z]{1,4}c|[a-z]{2,20}cie|[a-z]+commissie)$/',
        'projectgroep' => '/^[a-z]+(?<!pg)$/',
        'intro' => '/^intro-[a-z]+$/'
    ];

    /**
     * Checks if the given email is mutatable, which is true unless the
     * email domain ends with one of the domains listed in the `services.google.domains`
     * config directory, or a subdomain of one of those.
     * @param string $email
     * @return bool
     */
    public function canMutate(string $email): bool
    {
        // Get email list
        $domains = Config::get('services.google.domains');
        $emailDomain = Str::lower(Str::afterLast($email, '@'));

        // Disallow if own domain
        if (\in_array($emailDomain, $domains)) {
            return false;
        }

        // Disallow if subdomain of own domain
        $subdomains = \array_map(
            static fn ($domain) => ".{$domain}",
            $domains
        );

        // Check if it ends with our domain
        if (Str::endsWith($emailDomain, $subdomains)) {
            return false;
        }

        // Modifying allowed
        return true;
    }

    /**
     * Returns true if the given email is likely a mailing list we can modify via API
     * @param string $email
     * @return bool
     */
    public function canProcessList(string $email)
    {
        // Get email list
        $domains = Config::get('services.google.domains');
        $emailDomain = Str::lower(Str::afterLast($email, '@'));

        // Disallow if own domain
        return \in_array($emailDomain, $domains);
    }

    /**
     * Checks if the email address for the given mailing list matches the expectations
     * @param string $name
     * @param string $email
     * @return bool
     */
    public function validateListNameAgainstEmail(string $name, string $email): bool
    {
        // Get more predictable name
        $name = Str::lower(Str::ascii($name));
        $emailName = Str::before($email, '@');

        // Check what to match
        $validEmailPairs = [
            // Intro is `intro commissie`, so match this first!
            'intro' => ['intro'],

            // Rest
            'commissie' => ['commissie'],
            'projectgroep' => ['projectgroep', 'pg'],
        ];

        // Regex to use
        $matchedRegex = null;
        foreach ($validEmailPairs as $validRegex => $matches) {
            // Test if the string contians the given match
            if (!Str::contains($name, $matches)) {
                continue;
            }

            // Assign and stop looping
            $matchedRegex = $this->validEmails[$validRegex];
            break;
        }

        // Apply regex if it matches
        if ($matchedRegex && !\preg_match($matchedRegex, $emailName)) {
            return false;
        }

        // No reason not to allow it
        return true;
    }
}
