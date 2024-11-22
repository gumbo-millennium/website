<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * App\Models\ModelExport.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|int $generated_at
 * @property null|int $expires_at
 * @property string $model_type
 * @property int $model_id
 * @property null|int $user_id
 * @property string $job
 * @property null|string $disk
 * @property null|string $path
 * @property null|string $name
 * @property-read Eloquent|Model $model
 * @property-read null|\App\Models\User $user
 * @method static \Database\Factories\ModelExportFactory factory($count = null, $state = [])
 * @method static Builder|ModelExport forModel(\Illuminate\Database\Eloquent\Model $model)
 * @method static Builder|ModelExport forUser(\App\Models\User $user)
 * @method static Builder|ModelExport newModelQuery()
 * @method static Builder|ModelExport newQuery()
 * @method static Builder|ModelExport query()
 * @method static Builder|ModelExport whereIncomplete()
 * @mixin Eloquent
 */
class ModelExport extends Model
{
    use HasFactory;
    use Prunable;

    protected $casts = [
        'generated_at' => 'timestamp',
        'expires_at' => 'timestamp',
    ];

    public static function disk()
    {
        return config('gumbo.storage.disk');
    }

    protected static function booted(): void
    {
        self::saving(function (self $row) {
            if ($row->path != null) {
                $row->generated_at ??= now();
                $row->expires_at ??= now()->addMonths(6);
            }
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForModel(Builder $builder, Model $model): void
    {
        $builder->whereMorphedTo('model', $model);
    }

    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    public function scopeWhereIncomplete(Builder $query): void
    {
        $query->whereNull('generated_at');
    }

    public function prunable(): Builder
    {
        return static::where(fn ($query) => $query
            ->whereNotNull('expires_at'))
            ->where(
                'expires_at',
                '<=',
                now(),
            );
    }

    public function deleteFile(): void
    {
        if ($this->file === null) {
            return;
        }

        try {
            Storage::disk($this->disk)->delete($this->path);
        } catch (FileNotFoundException) {
            // noop
        }
    }

    public function saveFile(File|string $file): void
    {
        if (! $this->user) {
            throw new RuntimeException('User not set');
        }

        if (is_string($file)) {
            $file = new File($file);
        }

        if ($this->file != null) {
            $this->deleteFile();
        }

        $basePath = sprintf('%s/%04d', config('gumbo.exports.path'), $this->user->id);

        $this->generated_at = now();
        $this->name = $file->getBasename();

        $this->disk = config('gumbo.exports.disk');
        $this->path = Storage::disk($this->disk)->putFile($basePath, $file, [
            'visibility' => 'private',
        ]);
    }

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        $this->deleteFile();
    }
}
