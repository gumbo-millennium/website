<?php

declare(strict_types=1);

namespace App\Bots\Models;

/**
 * @method self text(string $text)
 * @method self parseMode(string $parseMode)
 * @method self disableWebPagePreview(bool $disableWebPagePreview)
 * @method self disableNotification(bool $disableNotification)
 * @method self allowSendingWithoutReply(bool $allowSendingWithoutReply)
 * @method static self make(array|string $text = [])
 * @codeCoverageIgnore
 */
class Message extends TelegramObject
{
    public function __construct($text = [])
    {
        if (is_string($text)) {
            $text = [
                'text' => $text,
            ];
        }

        parent::__construct($text);
    }
}
