<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $invisible = factory(Category::class, 5)->create([
            'visible' => 0,
        ]);

        $visible = factory(Category::class, 5)->create([
            'visible' => 1,
        ]);

        $productFactory = factory(Product::class, 5)->state('with-variants');

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

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCategory()
    {
        $category = factory(Category::class)->create([
            'visible' => 1,
        ]);
        $category2 = factory(Category::class)->create([
            'visible' => 1,
        ]);

        $base = [
            'visible' => 1,
            'category_id' => $category->id,
        ];
        $visibleProducts = factory(Product::class)->state('with-variants')->createMany([
            array_merge($base, ['name' => 'Visible Product 1']),
            array_merge($base, ['name' => 'Visible Product 2']),
            array_merge($base, ['name' => 'Visible Product 3']),
            array_merge($base, ['name' => 'Visible Product 4']),
        ]);

        $base = [
            'visible' => 0,
            'category_id' => $category->id,
        ];
        $invisibleProducts = factory(Product::class)->state('with-variants')->createMany([
            array_merge($base, ['name' => 'Hidden Product 1']),
            array_merge($base, ['name' => 'Hidden Product 2']),
            array_merge($base, ['name' => 'Hidden Product 3']),
            array_merge($base, ['name' => 'Hidden Product 4']),
        ]);


        $base = [
            'visible' => 1,
            'category_id' => $category2->id,
        ];
        $mismatchedProducts = factory(Product::class)->state('with-variants')->createMany([
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

    public function testVariantRequirement()
    {
        $category = factory(Category::class)->create([
            'visible' => 1,
        ]);
        $product = factory(Product::class)->create([
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

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testDisplay()
    {
        $category = factory(Category::class)->create([
            'visible' => 1,
        ]);

        $product = factory(Product::class)->state('with-variants')->create([
            'category_id' => $category->id,
            'visible' => 1,
        ]);

        $variants = factory(ProductVariant::class, 5)->create([
            'product_id' => $product->id,
        ]);

        $product->refresh();

        $productPath = route('shop.product', [
            'product' => $product->slug,
        ]);

        $variant = $product->default_variant;
        $productVariantUrl  = route('shop.product-variant', [
            'product' => $product->slug,
            'variant' => $variant->slug,
        ]);

        $this->onlyForMembers($productPath)
            ->assertRedirect($productVariantUrl);

        $response = $this->onlyForMembers($productVariantUrl)
            ->assertOk();

        $response->assertSee($category->name);
        $response->assertSee($product->name);
        $response->assertSee($variant->description ?? $product->description);

        foreach ($variants as $variant) {
            $response->assertSeeText($variant->name);
        }
    }
}
