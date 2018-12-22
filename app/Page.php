<?php
declare(strict_types=1);

namespace App;

use Corcel\Model\Page as CorcelPage;
use App\Concerns\PostContentTrait;
use Illuminate\Database\Eloquent\Builder;
use Corcel\Model\Option;

/**
 * WordPress Page with more features
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Page extends CorcelPage
{
    use PostContentTrait;

    public function scopePrivacyPolicy(Builder $builder)
    {
        return $builder
            ->where('ID', '=', Option::get('wp_page_for_privacy_policy'))
            ->limit(1);
    }
}
