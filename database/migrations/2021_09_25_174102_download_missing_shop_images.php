<?php

declare(strict_types=1);

use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use App\Support\Traits\DownloadsImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\URL;

class DownloadMissingShopImages extends Migration
{
    use DownloadsImages;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Download the image for each URL
        foreach (Product::query()->whereNotNull('image_path')->cursor() as $product) {
            if (! URL::isValidUrl($product->image_path)) {
                continue;
            }

            $product->image_path = $this->downloadImage($product->image_path);
            $product->save();
        }

        // Download the image for each URL
        foreach (ProductVariant::query()->whereNotNull('image_path')->cursor() as $product) {
            if (! URL::isValidUrl($product->image_path)) {
                continue;
            }

            $product->image_path = $this->downloadImage($product->image_path);
            $product->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
