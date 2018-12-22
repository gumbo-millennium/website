<?php
declare(strict_types=1);

namespace App;

use App\Post;

/**
 * Activities, organised by commissions authorized to do this.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Activity extends Post
{
    /**
     * Only target events
     * @var string
     */
    protected $postType = 'gumbo-activity';
}
