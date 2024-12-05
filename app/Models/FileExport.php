<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\Models\FileExport.
 *
 * @property int $id
 * @property string $urlkey
 * @property null|int $owner_id
 * @property string $path
 * @property string $filename
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $expires_at
 * @property-read bool $is_expired
 * @property-read bool $is_valid_export
 * @property-read null|\App\Models\User $owner
 * @method static \Database\Factories\FileExportFactory factory($count = null, $state = [])
 * @method static Builder|FileExport newModelQuery()
 * @method static Builder|FileExport newQuery()
 * @method static Builder|FileExport query()
 * @method static Builder|FileExport wherePurgeable()
 * @mixin \Eloquent
 */
class FileExport extends Model implements Responsable
{
    use HasFactory;
    use Prunable;

    private const TARGET_DIR = 'private/exports';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'owner_id' => 'int',
        'expires_at' => 'datetime',
    ];

    /**
     * Override saving to assign a random URL key.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(static function (self $model) {
            $model->urlkey = (string) Str::uuid();
        });
    }

    /**
     * Safely and quickly check for owner.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner && $this->owner->is($user);
    }

    /**
     * Owner of this export, and the only one allowed
     * to download it.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Expired downloads can never be downloaded.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at < Date::now();
    }

    public function getIsValidExportAttribute(): bool
    {
        return Str::startsWith($this->path, self::TARGET_DIR);
    }

    /**
     * Purgeable items, that can be removed from disk.
     */
    public function scopeWherePurgeable(Builder $builder): Builder
    {
        return $builder->where(
            static fn (Builder $query) => $query
                ->whereDoesntHave('owner')
                ->orWhere('expires_at', '<', Date::today()->subWeek()),
        );
    }

    /**
     * Set the file, needs an actual file.
     */
    public function attachFile(File $file, ?string $filename = null): self
    {
        $this->filename = Str::ascii(basename($filename ?? $file->getBasename()));

        $this->path = Storage::putFile(self::TARGET_DIR, $file);

        return $this;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        abort_if(! $this->isOwner($request->user()), 404);

        abort_if($this->is_expired, 410);

        if (! $this->is_valid_export) {
            Log::warning('Received a request for a file stored in {path}, which seems invalid', [
                'path' => $this->path,
                'model' => $this,
            ]);

            abort(410);
        }

        abort_if(! Storage::exists($this->path), 410);

        return Storage::download($this->path, $this->filename);
    }

    /**
     * Key by the urlkey, which is a UUID.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'urlkey';
    }

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return $this->query()->wherePurgeable();
    }

    /**
     * Remove existing files when pruning.
     * @return void
     */
    public function pruning()
    {
        if ($this->is_valid_export && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }
    }
}
