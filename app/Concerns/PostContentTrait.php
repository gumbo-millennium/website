<?php
declare (strict_types = 1);

namespace App\Concerns;

/**
 * Overrides the content trait to provide the filtered content. This contains
 * the content as parsed by WordPress.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait PostContentTrait
{
    /**
     * @return string
     */
    public function getContentAttribute()
    {
        if (!empty($this->post_content_filtered)) {
            return $this->stripShortcodes($this->post_content_filtered);
        } else {
            return $this->stripShortcodes($this->post_content);
        }
    }
}
