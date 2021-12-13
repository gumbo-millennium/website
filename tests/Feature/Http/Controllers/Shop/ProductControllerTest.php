<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;
    use TestsShop;
    use WithFaker;

    public function test_index(): void
    {
        $invisible = Category::factory(5)->create([
            'visible' => 0,
        ]);

        $visible = Category::factory(5)->create([
            'visible' => 1,
        ]);

        $productFactory = Product::factory(5)->withVariants();

        foreach ($visible as $category) {
            $productFactory->create([
                'category_id' => $category->id,
            ]);
        }

        $response = $this->onlyForMembers(route('shop.home'))
            ->assertOk();

        foreach ($visible as $category) {
            $response->assertSeeText($category->name);
        }
        foreach ($invisible as $category) {
            $response->assertDontSeeText($category->name);
        }
    }

    public function test_homepage_with_advertisement(): void
    {
        $product = $this->getProductVariant()->product;
        $product->advertise_on_home = true;
        $product->save();

        $this->onlyForMembers(route('shop.home'))
            ->assertOk()
            ->assertSeeText($product->name);
    }

    public function test_category(): void
    {
        $category = Category::factory()->create([
            'visible' => 1,
        ]);
        $category2 = Category::factory()->create([
            'visible' => 1,
        ]);

        $base = [
            'visible' => 1,
            'category_id' => $category->id,
        ];
        $visibleProducts = Product::factory()->withVariants()->createMany([
            array_merge($base, ['name' => 'Visible Product 1']),
            array_merge($base, ['name' => 'Visible Product 2']),
            array_merge($base, ['name' => 'Visible Product 3']),
            array_merge($base, ['name' => 'Visible Product 4']),
        ]);

        $base = [
            'visible' => 0,
            'category_id' => $category->id,
        ];
        $invisibleProducts = Product::factory()->withVariants()->createMany([
            array_merge($base, ['name' => 'Hidden Product 1']),
            array_merge($base, ['name' => 'Hidden Product 2']),
            array_merge($base, ['name' => 'Hidden Product 3']),
            array_merge($base, ['name' => 'Hidden Product 4']),
        ]);

        $base = [
            'visible' => 1,
            'category_id' => $category2->id,
        ];
        $mismatchedProducts = Product::factory()->withVariants()->createMany([
            array_merge($base, ['name' => 'Other Product 1']),
            array_merge($base, ['name' => 'Other Product 2']),
            array_merge($base, ['name' => 'Other Product 3']),
            array_merge($base, ['name' => 'Other Product 4']),
        ]);

        $response = $this->onlyForMembers(route('shop.category', ['category' => $category]))
            ->assertOk();

        foreach ($visibleProducts as $category) {
            $response->assertSeeText($category->name);
        }
        foreach ($invisibleProducts as $category) {
            $response->assertDontSeeText($category->name);
        }
        foreach ($mismatchedProducts as $category) {
            $response->assertDontSeeText($category->name);
        }
    }

    public function test_variant_requirement(): void
    {
        $category = Category::factory()->create([
            'visible' => 1,
        ]);
        $product = Product::factory()->create([
            'visible' => 1,
            'category_id' => $category->id,
        ]);

        $this->onlyForMembers(route('shop.home'))
            ->assertOk()
            ->assertDontSee($category->name);

        $this->onlyForMembers(route('shop.category', compact('category')))
            ->assertNotFound();

        $this->onlyForMembers(route('shop.product', compact('product')))
            ->assertNotFound();
    }

    public function test_display(): void
    {
        $category = Category::factory()->create([
            'visible' => 1,
        ]);

        $product = Product::factory()->withVariants()->create([
            'category_id' => $category->id,
            'visible' => 1,
            'description' => implode("\n", $this->faker->sentences(4)),
        ]);

        $variants = ProductVariant::factory(5)->create([
            'product_id' => $product->id,
            'description' => null,
        ]);

        $product->refresh();

        $productPath = route('shop.product', [
            'product' => $product->slug,
        ]);

        $variant = $product->default_variant;
        $productVariantUrl = route('shop.product-variant', [
            'product' => $product->slug,
            'variant' => $variant->slug,
        ]);

        $this->onlyForMembers($productPath)
            ->assertRedirect($productVariantUrl);

        $response = $this->onlyForMembers($productVariantUrl)
            ->assertOk();

        $response->assertSee($category->name);
        $response->assertSee($product->name);
        $response->assertSee($variant->description_html ?? $product->description_html);

        foreach ($variants as $variant) {
            $response->assertSeeText($variant->name);
        }
    }
}
