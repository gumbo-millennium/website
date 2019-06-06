<?php
declare(strict_types=1);

namespace App\Shortcodes;

use App\Models\Sponsor;
use Corcel\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

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
    public function render(ShortcodeInterface $shortcode) : ?string
    {
        // Only render one sponsor per page
        if ($this->rendered) {
            return null;
        }

        // Flag as rendered
        $this->rendered = true;

        // Find a random sponsor
        $sponsor = Sponsor::query()
            ->available()
            ->inRandomOrder()
            ->first();

        // Return empty if no sponsor was found
        if ($sponsor == null) {
            return null;
        }

        // TODO Update view count

        // Render the sponsor block
        return view(
            $sponsor->classic ? 'sponsor.classic' : 'sponsor.modern',
            ['sponsor' => $sponsor]
        )->render();
    }
}
