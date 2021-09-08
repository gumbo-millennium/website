<?php

declare(strict_types=1);

namespace App\Bots\Models;

use App\Helpers\Str;
use Illuminate\Support\Fluent;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * @method self disableNotification(bool $disableNotification)
 * @method self replyToMessageId(string $replyToMessageId)
 * @method self allowSendingWithoutReply(bool $allowSendingWithoutReply)
 * @codeCoverageIgnore
 */
abstract class TelegramObject extends Fluent
{
    /**
     * Makes a new Telegram object to send.
     *
     * @return static
     */
    public static function make(...$args)
    {
        return new static(...$args);
    }

    /**
     * Convert the fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect($this->getAttributes())
            ->mapWithKeys(fn ($val, $key) => [Str::snake($key) => $val])
            ->all();
    }

    /**
     * Adds a row of buttons underneath the message.
     *
     * @param Button[] $buttons
     * @return static
     */
    public function addKeyboardRow(Button ...$buttons): self
    {
        $this->replyMarkup ??= (new Keyboard())->inline();

        $this->replyMarkup->row(...$buttons);

        return $this;
    }

    /**
     * Forces a reply to the bot.
     * @return static
     */
    public function forceReply(bool $selective = true): self
    {
        $this->replyMarkup = Keyboard::forceReply([
            'selective' => $selective,
        ]);

        return $this;
    }
}
