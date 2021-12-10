<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Shop\Category;
use App\Models\Shop\Order;
use App\Models\Shop\OrderProduct;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        Order::query()->forceDelete();
        ProductVariant::query()->forceDelete();
        Product::query()->forceDelete();
        Category::query()->forceDelete();

        // Categories
        $categoryCount = $faker->numberBetween(2, 5);
        $categories = factory(Category::class, $categoryCount)
            ->make();
        $categories->each->save();
        $categories->push(null);

        // Keep track of the variants
        $variantSets = [];

        // Make products in each category
        foreach ($categories as $category) {
            $productCount = $faker->numberBetween(2, 12);
            $products = factory(Product::class, $productCount)->make([
                'category_id' => optional($category)->id,
            ]);
            $products->each->save();

            // Products can /not/ have variants, right?
            $variantCount = $faker->numberBetween(0, 6);
            if ($variantCount === 0) {
                continue;
            }

            // Make variants for each product
            foreach ($products as $product) {
                $variants = factory(ProductVariant::class, $variantCount)->make([
                    'product_id' => $product->id,
                ]);
                $variants->each->save();
                $variantSets[] = $variants->all();
            }
        }

        $variants = collect($variantSets)->collapse();
        assert($variants instanceof Collection);

        // Make orders
        $orderCount = $faker->numberBetween(5, 50);
        for ($i = 0; $i < $orderCount; $i++) {
            $order = factory(Order::class)->make();

            $products = $variants->random($faker->numberBetween(0, 7));

            $order->price = $products->sum('price');
            $order->save();

            $productMap = $products
                ->mapWithKeys(static fn (ProductVariant $variant) => [
                    $variant->id => new OrderProduct([
                        'quantity' => 1,
                        'price' => $variant->price,
                    ]),
                ]);
            $order->variants()->sync($productMap);

            $order->save();
        }
    }
}
