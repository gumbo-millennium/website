<?php

declare(strict_types=1);

use App\Models\Shop\ProductVariant;
use App\Support\Traits\DownloadsImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class RenameImageUrlToImagePathOnShopProductVariantsTable extends Migration
{
    use DownloadsImages;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->renameColumn('image_url', 'image_path');
        });

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
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->renameColumn('image_path', 'image_url');
        });
    }
}
