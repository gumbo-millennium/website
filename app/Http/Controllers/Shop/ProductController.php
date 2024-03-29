<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    /**
     * Get the item advertised in the shop.
     */
    public static function getAdvertisedProduct(): ?Product
    {
        return  Product::query()
            ->where([
                'visible' => 1,
                'advertise_on_home' => 1,
            ])
            ->with([
                'category',
                'variants',
            ])
            ->whereHas('category', fn (Builder $query) => $query->whereVisible(true))
            ->has('variants')
            ->orderByDesc('created_at')
            ->first();
    }

    public function index()
    {
        $categories = Category::query()
            ->where('visible', 1)
            ->has('products.variants')
            ->orderBy('name')
            ->get();

        $advertisedProduct = self::getAdvertisedProduct();

        return Response::view('shop.index', [
            'categories' => $categories,
            'advertisedProduct' => $advertisedProduct,
        ]);
    }

    public function showCategory(Category $category)
    {
        if (! $category->visible || ! $category->products()->has('variants')->exists()) {
            throw new NotFoundHttpException();
        }

        $products = $category->products()
            ->where('visible', '1')
            ->orderBy('name')
            ->with('variants')
            ->withCount('variants')
            ->has('variants')
            ->get();

        // TODO: show category
        return Response::view('shop.category', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function showProduct(Product $product)
    {
        if (! $product->visible || ! $product->category_id) {
            throw new NotFoundHttpException();
        }

        // Find first variant
        abort_unless($product->default_variant, HttpResponse::HTTP_NOT_FOUND);

        return Response::redirectToRoute('shop.product-variant', [
            'product' => $product,
            'variant' => $product->default_variant->slug,
        ]);
    }

    public function showProductVariant(Product $product, string $variant)
    {
        if (! $product->visible || ! $product->category_id) {
            throw new NotFoundHttpException();
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('slug', $variant)
            ->firstOrFail();

        // Add to CSP
        $this->addImageUrlsToCspPolicy([
            $product->image_url,
            $variant->image_url,
        ]);

        // Show product
        return Response::view('shop.product', [
            'category' => $product->category,
            'product' => $product,
            'variant' => $variant,
            'variants' => $product->variants,
        ]);
    }
}
