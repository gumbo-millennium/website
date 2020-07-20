<?php

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
     * @var string
     */
    private string $validCommitteeEmails = '/^(?:[a-z]{1,4}c|[a-z]{2,20}cie|[a-z]+commissie)$/';

    /**
     * Valid project group handles
     * @var string
     */
    private string $validProjectGroupEmails = '/^[a-z]+(?<!pg)$/';

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

        // If the email is for a committee, expect it to be short and end with a "C"
        if (Str::contains($name, 'commissie') && !\preg_match($this->validCommitteeEmails, $emailName)) {
            return false;
        }

        // If the email is for a projectgroep, ensure it does not end in 'pg'
        if (Str::contains($name, ['projectgroep', 'pg']) && !\preg_match($this->validProjectGroupEmails, $emailName)) {
            return false;
        }

        // No reason not to allow it
        return true;
    }
}
