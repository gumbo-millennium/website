<?php

declare(strict_types=1);

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Telegram\Chat
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $chat_id
 * @property string $type
 * @property string|null $name
 * @method static \Database\Factories\Telegram\ChatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat query()
 * @mixin \Eloquent
 */
class Chat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'telegram_chats';

    /**
     * Finds a single chat, or preps to create a new one.
     * @param string $chatId
     * @return Chat
     */
    public static function forChat(string $chatId): self
    {
        return self::firstOrNew(['chat_id' => $chatId]);
    }
}
