<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use App\Services\InventoryService;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateShopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        shop:update
            {--prune : Remove unmatched items}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the shop products and variants';

    protected ?GuzzleClient $client = null;

    /**
     * Execute the console command.
     */
    public function handle(InventoryService $service, GuzzleClient $client)
    {
        $this->client = $client;

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
        $seenCategoryIds = [];

        $this->line(sprintf(
            'Retrieved <info>%d</> products from API',
            count($products),
        ), null, OutputInterface::VERBOSITY_VERBOSE);

        foreach ($products as $product) {
            // Get product
            $model = Product::firstOrNew([
                'id' => Arr::get($product, 'uuid'),
            ]);

            $model->fill([
                'name' => Arr::get($product, 'name'),
                'etag' => Arr::get($product, 'etag'),
                'vat_rate' => (int) Arr::get($product, 'vatPercentage'),
            ]);

            // Assign the description if none is set
            $model->description ??= Arr::get($product, 'description');

            // Safely get image
            $imagePath = $this->downloadImageUrl(Arr::get($product, 'presentation.imageUrl'));
            if ($imagePath) {
                $model->image_path = $imagePath;
            }

            $category = Arr::get($product, 'category');
            if ($category) {
                $model->category()->associate(
                    Category::updateOrCreate([
                        'id' => $category['uuid'],
                    ], [
                        'name' => $category['name'],
                    ]),
                );

                $seenCategoryIds[] = $category['uuid'];
            }

            // Save changes
            $model->save();

            $this->line(sprintf(
                'Created product <info>%s</>',
                $model->name,
            ), null, OutputInterface::VERBOSITY_VERBOSE);

            // Keep track of IDs
            $seenProductIds[] = $model->id;

            // Update the variants
            $seenVariantIds[] = $this->updateVariants($model, Arr::get($product, 'variants'));
        }

        $this->line(sprintf(
            'Created or updated <info>%d</> products',
            count($seenProductIds),
        ));

        // Prune if requested
        if (! $this->option('prune')) {
            return;
        }

        $productCount = Product::whereNotIn('id', $seenProductIds)->delete();
        $variantCount = ProductVariant::whereNotIn('id', Arr::collapse($seenVariantIds))->delete();
        $categoryCount = Category::whereNotIn('id', $seenCategoryIds)->delete();

        $this->line(sprintf(
            'Deleted <info>%s</> products, <info>%d</> variants and <info>%d</> categories.',
            $productCount,
            $variantCount,
            $categoryCount,
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

            // Safely get image
            $imagePath = $this->downloadImageUrl(Arr::get($product, 'presentation.imageUrl'));
            if ($imagePath) {
                $model->image_path = $imagePath;
            }

            // Enure pairing
            $model->product()->associate($product);

            $model->save();

            $this->line(sprintf(
                'Created variant <info>%s</> for <comment>%s</>',
                $model->name,
                $product->name,
            ), null, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $seenVariantIds[] = $model->id;
        }

        return $seenVariantIds;
    }

    /**
     * Check if the file is found, and not empty.
     */
    private function downloadImageUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $this->line("Downloading file from [<info>{$url}</>]...", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

        $sinkFile = tempnam(sys_get_temp_dir(), 'zettle-shop-image');

        try {
            $response = $this->client->get($url, [
                RequestOptions::TIMEOUT => 5,
                RequestOptions::SINK => $sinkFile,
                RequestOptions::ALLOW_REDIRECTS => [
                    'max' => 3,
                    'referer' => true,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return Storage::disk(Config::get('gumbo.images.disk'))
                ->putFile(path_join(Config::get('gumbo.images.path'), 'shop/images'), new File($sinkFile));
        } catch (GuzzleException $exception) {
            return null;
        } finally {
            if (file_exists($sinkFile)) {
                @unlink($sinkFile);
            }
        }
    }
}
