<?php
declare(strict_types=1);

namespace App;

use App\Post;

/**
 * Sponsors, custom blocks of HTML
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Sponsor extends Post
{
    /**
     * Only target sponsors
     * @var string
     */
    protected $postType = 'gumbo-sponsor';
}
