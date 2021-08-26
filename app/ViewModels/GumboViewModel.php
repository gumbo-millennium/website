<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Helpers\Str;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Spatie\ViewModels\ViewModel;

abstract class GumboViewModel extends ViewModel
{
    private const METHOD_REGEX = '/^get([A-Z][a-zA-Z0-9]+)Attribute$/';

    /**
     * Extends Spatie's ViewModel system to automagically add the
     * get<Name>Attribute methods if they're protected, and map them
     * to a snake case variant.
     */
    protected function items(): Collection
    {
        // Get reflection class
        $class = new ReflectionClass($this);

        // Check all protected methods
        $protectedMethods = collect($class->getMethods(ReflectionMethod::IS_PROTECTED))
                // phpcs:ignore Generic.Files.LineLength.TooLong
            ->filter(static fn (ReflectionMethod $method) => preg_match(self::METHOD_REGEX, $method->getName()))
            ->mapWithKeys(function (ReflectionMethod $method) {
                // Remove 'get' and 'Attribute' and convert to snake-case
                $name = Str::snake(preg_replace('/^get|Attribute$/', '', $method->getName()));

                // Get value from method
                return [$name => $this->createVariableFromMethod($method)];
            });

        // Merge with parent
        return parent::items()->merge($protectedMethods);
    }
}
