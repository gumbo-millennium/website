<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateShop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:update-shop
        {--prune : Remove unmatched items}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the shop products and variants';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(InventoryService $service)
    {
        try {
            Product::unguard();
            ProductVariant::unguard();
            Category::unguard();

            $this->updateProducts($service);
        } finally {
            Product::reguard();
            ProductVariant::reguard();
            Category::reguard();
        }
    }

    protected function updateProducts(InventoryService $service)
    {
        $products = $service->getJson('https://products.izettle.com/organizations/self/products/v2');

        $seenProductIds = [];
        $seenVariantIds = [];

        $this->line(sprintf(
            'Retrieved <info>%d</> products from API',
            count($products)
        ), null, OutputInterface::VERBOSITY_VERBOSE);

        foreach ($products as $product) {
            // Get product
            $model = Product::firstOrNew([
                'id' => Arr::get($product, 'uuid'),
            ]);

            $model->fill([
                'name' => Arr::get($product, 'name'),
                'description' => Arr::get($product, 'description'),
                'etag' => Arr::get($product, 'etag'),
                'vat_rate' => (int) Arr::get($product, 'vatPercentage'),
                'image_url' => Arr::get($product, 'presentation.imageUrl'),
            ]);

            $category = Arr::get($product, 'category');
            if ($category) {
                $model->category()->associate(
                    Category::firstOrCreate([
                        'name' => $category,
                    ])
                );
            }

            // Save changes
            $model->save();

            $this->line(sprintf(
                'Created product <info>%s</>',
                $model->name
            ), null, OutputInterface::VERBOSITY_VERBOSE);

            // Keep track of IDs
            $seenProductIds[] = $model->id;

            // Update the variants
            $seenVariantIds[] = $this->updateVariants($model, Arr::get($product, 'variants'));
        }

        $this->line(sprintf(
            'Created or updated <info>%d</> products',
            count($seenProductIds)
        ));

        // Prune if requested
        if (!$this->option('prune')) {
            return;
        }

        $productCount = Product::whereNotIn('id', $seenProductIds)->delete();
        $variantCount = ProductVariant::whereNotIn('id', Arr::collapse($seenVariantIds))->delete();

        $this->line(sprintf(
            'Deleted <info>%s</> products and <info>%d</> variants.',
            $productCount,
            $variantCount
        ));
    }

    protected function updateVariants(Product $product, array $variants): array
    {
        // Get product
        $seenVariantIds = [];
        foreach ($variants as $variant) {
            $model = ProductVariant::firstOrNew([
                'id' => Arr::get($variant, 'uuid'),
            ]);

            $model->name = Arr::get($variant, 'name') ?: $product->name;
            $model->description = Arr::get($variant, 'description');
            $model->sku = Arr::get($variant, 'sku');
            $model->price = Arr::get($variant, 'price.amount');
            $model->image_url = Arr::get($variant, 'presentation.imageUrl');

            // Enure pairing
            $model->product()->associate($product);

            $model->save();

            $this->line(sprintf(
                'Created variant <info>%s</> for <comment>%s</>',
                $model->name,
                $product->name
            ), null, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $seenVariantIds[] = $model->id;
        }

        return $seenVariantIds;
    }
}