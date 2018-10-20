<?php
declare(strict_types=1);

namespace App;

use Corcel\Model\Post as CorcelPost;
use App\Concerns\PostContentTrait;

/**
 * WordPress Post with more features
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Post extends CorcelPost
{
    use PostContentTrait;
}
