<?php

declare(strict_types=1);

namespace App\Fluent;

use DomainException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Fluent;
use JsonSerializable;
use OutOfRangeException;
use Stringable;

final class Image extends Fluent implements JsonSerializable, Stringable
{
    /** Default. Resizes the image to fit within the width and height
     * boundaries without cropping, distorting or altering the aspect ratio.
     */
    public const FIT_CONTAIN = 'contain';

    /** Resizes the image to fit within the width and height boundaries without
     * cropping, distorting or altering the aspect ratio, and will also not
     * increase the size of the image if it is smaller than the output size.
     */
    public const FIT_MAX = 'max';

    /** Resizes the image to fit within the width and height boundaries without
     * cropping or distorting the image, and the remaining space is filled with
     * the background color. The resulting image will match the constraining
     * dimensions.
     */
    public const FIT_FILL = 'fill';

    /**
     * Stretches the image to fit the constraining dimensions exactly. The
     * resulting image will fill the dimensions, and will not maintain the
     * aspect ratio of the input image.
     */
    public const FIT_STRETCH = 'stretch';

    /**
     * Resizes the image to fill the width and height boundaries and crops any
     * excess image data. The resulting image will match the width and height
     * constraints without distorting the image. See the crop page for more
     * information.
     */
    public const FIT_CROP = 'crop';

    /**
     * Default. Best for large images and photos.
     */
    public const FORMAT_JPG = 'jpg';

    /**
     * Transparent format, best for icons and small images.
     */
    public const FORMAT_PNG = 'png';

    /**
     * Gifs, for animation, usually not recommended.
     */
    public const FORMAT_GIF = 'gif';

    /**
     * Modern format that's smaller and works like a png. Recommended as modern
     * alternative in <figure> tags.
     */
    public const FORMAT_WEBP = 'webp';

    private const ATTRIBUTE_MAP = [
        'width' => 'w',
        'height' => 'h',
        'fit' => 'fit',
        'quality' => 'q',
        'format' => 'fm',
    ];

    private const VALID_FITS = [
        self::FIT_CONTAIN,
        self::FIT_MAX,
        self::FIT_FILL,
        self::FIT_STRETCH,
        self::FIT_CROP,
    ];

    private const VALID_FORMATS = [
        self::FIT_CROP,
        self::FORMAT_JPG,
        self::FORMAT_PNG,
        self::FORMAT_GIF,
        self::FORMAT_WEBP,
    ];

    /**
     * Flag to indicate the URLs should expire, making them auto-expire after 1 hour.
     * @param bool
     */
    private bool $shouldExpire = false;

    /**
     * Creates a new image for the given path.
     * @param string $path
     */
    public static function make(?string $path): self
    {
        return new self([
            'path' => $path,
        ]);
    }

    /**
     * Specify how this image will fit in the given width and height.
     * @throws DomainException if $fit is invalid
     */
    public function fit(string $fit): self
    {
        throw_unless(in_array($fit, self::VALID_FITS, true), DomainException::class, "Invalid fit type [${fit}] specified.");

        $this['fit'] = $fit;

        return $this;
    }

    /**
     * Specify the output format to transcode to.
     * @throws DomainException if $format is invalid
     */
    public function format(string $format): self
    {
        throw_unless(in_array($format, self::VALID_FORMATS, true), DomainException::class, "Invalid format [${format}] specified.");

        $this['fm'] = $format;

        return $this;
    }

    /**
     * Specify the max width of the image. Use fit to determine how the image is sized.
     * @throws OutOfRangeException if $width is zero or negative
     */
    public function width(int $width): self
    {
        throw_if($width <= 0, OutOfRangeException::class, "Width needs to be larger than zero, {$width} given.");

        $this['w'] = $width;

        return $this;
    }

    /**
     * Specify the max height of the image. Use fit to determine how the image is sized.
     * @throws OutOfRangeException if $width is zero or negative
     */
    public function height(int $height): self
    {
        throw_if($height <= 0, OutOfRangeException::class, "Height needs to be larger than zero, {$height} given.");

        $this['h'] = $height;

        return $this;
    }

    /**
     * Make this image a square of the given size, excess space is cropped off.
     * @throws OutOfRangeException if $size is zero or negative
     */
    public function square(int $size): self
    {
        throw_if($size <= 0, OutOfRangeException::class, "Size needs to be larger than zero, ${size} given.");

        return $this->width($size)->height($size)->fit(self::FIT_CROP);
    }

    /**
     * Set quality, only works if format is jpeg.
     * @throws OutOfRangeException if quality isn't between 1 and 100, inclusive
     */
    public function quality(int $quality): self
    {
        throw_if($quality <= 0 || $quality > 100, OutOfRangeException::class, "Quality needs to be between 1 and 100 inclusive, {$quality} given.");

        $this['q'] = $quality;

        return $this;
    }

    /**
     * Transcode to jpeg with optional quality specified.
     */
    public function jpg(int $quality = 95): self
    {
        return $this->format(self::FORMAT_JPG)->quality($quality);
    }

    /**
     * Transcode to webp.
     */
    public function webp(): self
    {
        return $this->format(self::FORMAT_WEBP);
    }

    /**
     * Transcode to png.
     */
    public function png(): self
    {
        return $this->format(self::FORMAT_PNG);
    }

    /**
     * Apply a preset from the config.
     */
    public function preset(string $preset): self
    {
        // Get key
        $key = "gumbo.image-presets.{$preset}";
        if (! Config::has($key)) {
            throw new DomainException("Invalid preset [${preset}] specified.");
        }

        // Apply config and map it locally
        $preset = Config::get($key);
        foreach ($preset as $key => $value) {
            $property = self::ATTRIBUTE_MAP[$key] ?? $key;
            $this->attributes[$property] = $value;
        }

        // Done
        return $this;
    }

    /**
     * Flag to indicate the URLs should expire, making them auto-expire after 1 hour.
     */
    public function shouldExpire(bool $shouldExpire = true): self
    {
        $this->shouldExpire = $shouldExpire;

        return $this;
    }

    /**
     * Returns the signed URL to this image.
     */
    public function getUrl(): string
    {
        if (! $this->path) {
            return '';
        }

        if (URL::isValidUrl($this->path)) {
            return $this->path;
        }

        return URL::signedRoute(
            'image.render',
            $this->toArray(),
            $this->shouldExpire ? Date::now()->addHour() : null,
        );
    }

    public function jsonSerialize()
    {
        return $this->getUrl();
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
