<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ProductVariant;
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
