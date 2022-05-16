<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BackupType;
use App\Helpers\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * @property BackupType $type
 */
class Backup extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'type' => BackupType::class,
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'type',
    ];

    /**
     * Ensure all backups have a path when they're saved, in case the logic changes later on.
     * @return void
     */
    public static function booted()
    {
        static::creating(function (self $backup) {
            $backup->assignPath();
        });
    }

    public function previous(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_backup_id');
    }

    public function scopeExpired(Builder $builder): void
    {
        // Skip incomplete backups
        $builder->whereNotNull('completed_at');

        // Keep incremental backups younger than 16 days
        // and full backups younger than 6 months
        $this->where(function (Builder $builder) {
            $builder->where(fn (Builder $query) => $query->where([
                ['type', '=', BackupType::Incremental],
                ['completed_at', '<', Date::today()->subDays(Config::get('gumbo.backups.incremental_preservation_days'))],
            ]));

            $builder->orWhere(fn (Builder $query) => $query->where([
                ['type', '=', BackupType::Full],
                ['completed_at', '<', Date::today()->subDays(Config::get('gumbo.backups.full_preservation_days'))],
            ]));
        });
    }

    /**
     * Assigns the path of this backup, usually done just before creating the backup.
     */
    public function assignPath(): void
    {
        // Only set the path once
        if ($this->path !== null) {
            throw new RuntimeException('Backup path already assigned');
        }

        // Backup type is part of the path
        if (! $this->type) {
            throw new RuntimeException('Cannot determine path for a backup with no type.');
        }

        // Ensure the prefix is valid
        $pathPrefix = rtrim(Config::get('gumbo.backups.storage_path', ''), '/');
        if (empty($pathPrefix)) {
            throw new RuntimeException('Backup storage path is not configured or configured to be empty.');
        }

        // Compute timestamp and final path
        $timestamp = ($this->created_at ?? Date::now())->format('Y-m-d_H-i-s');
        $path = sprintf('%s/%s/%s', $pathPrefix, Str::lower($this->type->value), $timestamp);

        // Determine disk
        $disk = Config::get('gumbo.backups.storage_disk');

        // Ensure file is unique
        if (Storage::disk($disk)->exists($path)) {
            throw new RuntimeException("Backup path already exists: {$path}");
        }

        // Assign properties
        $this->disk = $disk;
        $this->path = $path;
    }
}
