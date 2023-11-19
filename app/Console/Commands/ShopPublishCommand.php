<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Arr;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShopPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
            shop:publish
                {product* : Products to publish}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the given product and variants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \Illuminate\Support\Collection<Product> $products */
        $products = Product::query()
            ->whereIn('id', $this->argument('product'))
            ->with([
                'variants',
                'category',
            ])
            ->get();

        if ($products->isEmpty()) {
            $this->error('Failed to find requested product(s).');

            return 1;
        }

        foreach ($products as $product) {
            $product->visible = true;
            $product->save();

            $this->line(sprintf(
                'Marked product <info>%s</> visible.',
                $product->name,
            ));
        }

        /** @var \Illuminate\Support\Collection<Category> $invisibleCategories */
        $invisibleCategories = Category::query()
            ->whereIn('id', $products->pluck('category.id')->toArray())
            ->where('visible', '!=', '1')
            ->get();

        foreach ($invisibleCategories as $category) {
            $category->visible = true;
            $category->save();

            $this->line(sprintf(
                'Marked category <info>%s</> visible.',
                $category->name,
            ));
        }
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (! $this->argument('product')) {
            $products = Product::query()
                ->where('visible', '!=', '1')
                ->with(['category'])
                ->orderBy('category_id')
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                return;
            }

            $productNames = [];
            $productIds = [];
            foreach ($products as $product) {
                $productName = sprintf('%s > %s', optional($product->category)->name ?? '--', $product->name);
                $productNames[] = $productName;
                $productIds[$productName] = $product->id;
            }

            $chosenProducts = $this->choice('Which products would you like to publish?', $productNames, null, 5, true);

            if ($chosenProducts) {
                $chosenProductIds = Arr::only($productIds, $chosenProducts);
                $this->input->setArgument('product', $chosenProductIds);
            }
        }
    }
}
