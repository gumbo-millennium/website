<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Shop;

use App\Models\Shop\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public static function htmlTestProvider(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'html tags' => ['<p>test</p>', 'test'],
            'newlines' => ["Hello\nWorld", "Hello<br />\nWorld"],
        ];
    }

    /**
     * @dataProvider htmlTestProvider
     */
    public function test_cast_normal_string(?string $input, ?string $output): void
    {
        $model = Product::unguarded(fn () => new Product([
            'description' => $input,
        ]));

        if ($output === null) {
            $this->assertNull($model->description_html);

            return;
        }

        $this->assertInstanceOf(HtmlString::class, $model->description_html);
        $this->assertSame($output, (string) $model->description_html);
    }

    public function test_computing_of_order_limit(): void
    {
        Config::set('gumbo.shop.order-limit', 4);

        $model = Product::factory()->create();

        $this->assertSame(4, $model->refresh()->applied_order_limit);

        $model->order_limit = 8;
        $model->save();

        $this->assertSame(8, $model->refresh()->applied_order_limit);

        $model->order_limit = 2;
        $model->save();

        $this->assertSame(2, $model->refresh()->applied_order_limit);
    }
}
