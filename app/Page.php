<?php
declare(strict_types=1);

namespace App;

use Corcel\Model\Page as CorcelPage;
use App\Concerns\PostContentTrait;

/**
 * WordPress Page with more features
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Page extends CorcelPage
{
    use PostContentTrait;

    /**
     * Returns the homepage
     * @return Page
     */
    public static function homepage() : ?self
    {
        $homepage = Option::get('page_on_front');
        if ($homepage) {
            return self::find($homepage);
        }
        return null;
    }
}
