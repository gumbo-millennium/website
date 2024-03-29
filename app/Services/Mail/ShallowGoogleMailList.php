<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Contracts\Mail\MailList;
use LogicException;

class ShallowGoogleMailList extends GoogleMailList
{
    public function listEmails(): array
    {
        throw new LogicException('Cannot list members on a shallow group');
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function addEmail(string $email, int $role = self::ROLE_NORMAL): void
    {
        throw new LogicException('Cannot add members on a shallow group');
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function updateEmail(string $email, int $role = self::ROLE_NORMAL): void
    {
        throw new LogicException('Cannot update members on a shallow group');
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function removeEmail(string $email): void
    {
        throw new LogicException('Cannot remove members on a shallow group');
    }

    public function getChangedEmails(): array
    {
        return [];
    }

    /**
     * Returns the full mail list.
     *
     * @return GoogleMailList|MailList
     * @throws BindingResolutionException
     */
    public function toFullList(): MailList
    {
        return \app(GoogleMailListService::class)
            ->getList($this->getEmail());
    }
}
