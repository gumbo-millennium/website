<?php

namespace App\Models;

use App\Models\FileCategory;
use App\Models\User;
use App\Traits\HasPaperclip;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasParent;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

/**
 * A user-uploaded file
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class File extends SluggableModel implements AttachableInterface
{
    use PaperclipTrait;
    use HasPaperclip;

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        'url'
    ];

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'file_contents',
        'file_pages',
        'file_meta',
        'pulled',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'pulled' => 'bool',
        'file_meta' => 'json',
        'file_pages' => 'int',
    ];

    /**
     * Binds paperclip files
     *
     * @return void
     */
    protected function bindPaperclip() : void
    {
        // The file itself
        $this->hasAttachedFile('file');

        // The screenshot sizes
        $containerWidth = 1280;
        $tileWidth = $containerWidth / 12 * 4;
        $mediumWidth = $containerWidth / 12 * 3;
        $squareSize = 192;

        // The actual screenshots
        $this->hasAttachedFile('thumbail', [
            'variants' => [
                // The tile variant is a 4/12 width image, which is wider than it is tall.
                Variant::make('medium')->steps(ResizeStep::make()->width($tileWidth)),
                Variant::make('medium@2x')->steps(ResizeStep::make()->width($tileWidth * 2)),

                // The medium variant is a sidebar element which, at best, takes up 3/12 of the container
                // On mobile it's full-width, but eh ¯\_(ツ)_/¯
                Variant::make('medium')->steps(ResizeStep::make()->width($mediumWidth)),
                Variant::make('medium@2x')->steps(ResizeStep::make()->width($mediumWidth * 2)),

                // Square picture, used in collection view or something
                Variant::make('square')->steps(ResizeStep::make()->square($squareSize)->crop()),
                Variant::make('square@2x')->steps(ResizeStep::make()->square($squareSize * 2)->crop()),
            ]
        ]);
    }

    /**
     * Generate the slug based on the display_title property
     *
     * @return array
     */
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'onUpdate' => false
            ]
        ];
    }

    /**
     * The roles that belong to the user.
     *
     * @return Relation
     */
    public function category() : Relation
    {
        return $this->belongsTo(FileCategory::class, 'category_id', 'id');
    }

    /**
     * A file has an owner
     *
     * @return Relation
     */
    public function owner() : Relation
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * A file may have downloads
     *
     * @return Relation
     */
    public function downloads() : Relation
    {
        return $this->hasMany(FileDownload::class);
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
     * A file may have a replacement
     *
     * @return Relation
     */
    public function replacement() : Relation
    {
        return $this->belongsTo(File::class, 'replacement_id');
    }
}
