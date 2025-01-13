<?php

declare(strict_types=1);

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Telegram\Chat.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $left_at
 * @property string $chat_id
 * @property string $type
 * @property null|string $name
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'left_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'left_at',
        'chat_id',
        'type',
        'name',
    ];

    /**
     * Finds a single chat, or preps to create a new one.
     */
    public static function forChat(string $chatId): self
    {
        return self::firstOrNew(['chat_id' => $chatId]);
    }
}
