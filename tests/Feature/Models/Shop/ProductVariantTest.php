<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Shop;

use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    /**
     * @dataProvider htmlTestProvider
     */
    public function test_cast_normal_string(?string $input, ?string $output): void
    {
        $model = ProductVariant::unguarded(fn () => new ProductVariant([
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

        $product = factory(Product::class)->create();
        $variant = $product->variants()->save(factory(ProductVariant::class)->make());

        $this->assertSame(4, $variant->refresh()->applied_order_limit);

        $product->order_limit = 7;
        $product->save();

        $this->assertSame(7, $variant->refresh()->applied_order_limit);

        $variant->order_limit = 3;
        $variant->save();

        $this->assertSame(3, $variant->refresh()->applied_order_limit);

        $product->order_limit = null;
        $variant->save();

        $this->assertSame(3, $variant->refresh()->applied_order_limit);
    }

    public function htmlTestProvider(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'html tags' => ['<p>test</p>', 'test'],
            'newlines' => ["Hello\nWorld", "Hello<br />\nWorld"],
        ];
    }
}
