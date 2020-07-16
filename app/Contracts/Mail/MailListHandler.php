<?php

declare(strict_types=1);

namespace App\Contracts\Mail;

interface MailListHandler
{
    /**
     * Returns all mailing lists
     * @return array<MailList>
     */
    public function getAllLists(): array;

    /**
     * Returns the list with the given email address
     * @param string $email
     * @return null|MailList
     */
    public function getList(string $email): ?MailList;

    /**
     * Returns a new list with the given email address. Will probably
     * throw an exception if it already exists
     * @param string $email
     * @param string $name
     * @return MailList
     */
    public function createList(string $email, string $name): MailList;

    /**
     * Saves changes to the given MailList
     * @param MailList $list
     * @return void
     */
    public function save(MailList $list): void;

    /**
     * Deletes the given maillist
     * @param MailList $list
     * @return void
     */
    public function delete(MailList $list): void;
}
