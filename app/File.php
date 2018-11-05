<?php

namespace App;

use App\FileCategory;
use App\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

/**
 * A user-uploaded file
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class File extends SluggableModel
{
    /**
     * Storage directory of files
     */
    const STORAGE_DIR = 'files';

    /**
     * @var int File is pending processing
     */
    const STATE_PENDING = 0;

    /**
     * @var int File was checked by repair system
     */
    const STATE_FILE_CHECKED = 1;

    /**
     * @var int File is converted to PDF/A format.
     */
    const STATE_PDFA = 4;

    /**
     * @var int File has metadata
     */
    const STATE_HAS_META = 8;

    /**
     * @var int File has thumbnails
     */
    const STATE_HAS_THUMBNAIL = 16;

    /**
     * @var int File is broken and cannot be published
     */
    const STATE_BROKEN = 1024;

    /**
     * @var string[] Names of the states
     */
    const STATES = [
        self::STATE_PENDING => 'pending',
        self::STATE_FILE_CHECKED => 'checked',
        self::STATE_BROKEN => 'broken',
        self::STATE_PDFA => 'pdfa',
        self::STATE_HAS_META => 'has-meta',
        self::STATE_HAS_THUMBNAIL => 'has-thumbnail',
    ];

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        'url',
        'display_title'
    ];

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'filename',
        'filesize',
        'mime',
        'path',
        'public'
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'file_meta' => 'array'
    ];

    /**
     * Generate the slug based on the display_title property
     *
     * @return array
     */
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'display_title',
                'unique' => true,
                'onUpdate' => true
            ]
        ];
    }

    /**
     * The roles that belong to the user.
     *
     * @return Relation
     */
    public function categories() : Relation
    {
        return $this->belongsToMany(FileCategory::class, 'file_category_catalog', 'file_id', 'category_id');
    }

    /**
     * A file has an owner
     *
     * @return Relation
     */
    public function owner() : Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A file may have downloads
     *
     * @return Relation
     */
    public function download() : Relation
    {
        return $this->belongsToMany(User::class, 'file_downloads')
            ->as('download')
            ->using(FileDownload::class);
    }

    /**
     * Returns the display title of a file, or null if unknown
     *
     * @return string|null
     */
    public function getDisplayTitleAttribute() : ?string
    {
        return !empty($this->title) ? $this->title : $this->filename;
    }

    /**
     * Prevents deletion after 48hrs of uploading.
     *
     * @return bool
     */
    public function getCanDeleteAttribute() : bool
    {
        // Always allow deletion of non-created files
        if ($this->created_at === null) {
            return true;
        }

        // Check category for

        // Get a timestamp 2 days back
        $twoDaysAgo = today()->subDays(2);

        // Allow deletion if not yet saved OR if created less than 2 days ago
        return $this->created_at === null  || $this->created_at >= $twoDaysAgo;
    }

    /**
     * Returns the absolute URL to the file
     *
     * @return string|null
     */
    public function getUrlAttribute() : ?string
    {
        // Ignore if slugless
        if ($this->slug === null) {
            return null;
        }

        return route('files.show', ['file' => $this]);
    }

    /**
     * Returns human-readable status
     *
     * @return array
     */
    public function getProcessingStatusAttribute() : array
    {
        if ($this->hasState(self::STATE_BROKEN)) {
            return [__('files.state.broken')];
        }

        $result = [];
        foreach (self::STATES as $value => $label) {
            if ($this->hasState($value)) {
                $result[] = __("files.state.$label");
            }
        }
        return $result;
    }

    /**
     * Ensures that the filename has a lowercase extension and
     * is ASCII-safe.
     *
     * @param string $filename
     * @return void
     */
    public function setFilenameAttribute(?string $filename)
    {
        if (is_string($filename)) {
            // Determine extension from mime, or assume PDF
            if (!empty($this->mime)) {
                $extguesser = ExtensionGuesser::getInstance();
                $ext = preg_quote($extguesser->guess($this->mime));
            } else {
                $ext = 'pdf';
            }

            // Trim extension
            if (preg_match("/^(.+)\.{$ext}$/i", $filename, $matches)) {
                $filename = $matches[1];
            }

            // Convert to ASCII, using Dutch locale
            setlocale(LC_CTYPE, 'nl_NL');
            $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
            $filename .= ".{$ext}";
        }

        // Store filename
        $this->attributes['filename'] = $filename;
    }

    /**
     * Adds the given state
     *
     * @param int $state
     * @return void
     */
    public function addState(int $state) : void
    {
        $this->state |= $state;
    }

    /**
     * Removes the given state
     *
     * @param int $state
     * @return void
     */
    public function removeState(int $state) : void
    {
        $this->state ^= $state;
    }

    /**
     * Checks if a given state is present on the file
     *
     * @param int $state
     * @return bool
     */
    public function hasState(int $state) : bool
    {
        return $state === 0 ? $this->state === 0 : ($this->state & $state) === $state;
    }

    /**
     * Returns true if file is broken.
     *
     * @return bool
     */
    public function getBrokenAttribute() : bool
    {
        return $this->hasState(self::STATE_BROKEN);
    }
}
