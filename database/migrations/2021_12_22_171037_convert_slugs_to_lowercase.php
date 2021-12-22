<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models;
use App\Models\SluggableModel;
use Illuminate\Database\Migrations\Migration;

class ConvertSlugsToLowercase extends Migration
{
    private const SLUG_MODELS = [
        Models\Activity::class,
        Models\FileBundle::class,
        Models\FileCategory::class,
        Models\NewsItem::class,
        Models\Shop\Category::class,
        Models\Shop\Product::class,
        Models\Shop\ProductVariant::class,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::SLUG_MODELS as $model) {
            if (! is_a($model, SluggableModel::class, true)) {
                throw new LogicException(
                    "Tried to clean slugs on {$model}, but it's not a sluggable model",
                );
            }

            $model::query()->withoutGlobalScopes()->chunk(100, function (iterable $models) {
                $slugKeys = [];

                /** @var SluggableModel $model */
                foreach ($models as $model) {
                    $slugKeys = array_keys($model->sluggable());

                    foreach ($slugKeys as $slug) {
                        $model->{$slug} = Str::lower($model->{$slug});
                    }

                    $model->save();
                }
            });
        }
    }
}
