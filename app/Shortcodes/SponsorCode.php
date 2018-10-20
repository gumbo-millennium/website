<?php
declare(strict_types=1);

namespace App\Shortcodes;

use App\Sponsor;
use Corcel\Shortcode;

/**
 * Handles showing a random sponsor using the shortcode
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class SponsorShortcode implements Shortcode
{
    /**
     * True if the request already has a sponsor renderd.
     *
     * @var bool
     */
    protected $rendered;

    /**
     * Renders a sponsor block
     *
     * @param ShortcodeInterface $shortcode
     * @return string
     */
    public function render(ShortcodeInterface $shortcode) : string
    {
        if ($this->rendered) {
            return null;
        }

        // Flag as rendered
        $this->rendered = true;

        // Find a random sponsor
        $this->sponsor = Sponsor::query()
            ->published()
            ->inRandomOrder()
            ->first();

        // Return safe content if a sponsor is available
        return $sponsor ? $sponsor->content : '';
    }
}
