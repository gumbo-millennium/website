<?php

declare(strict_types=1);

namespace App\ViewModels;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Spatie\ViewModels\ViewModel;

abstract class GumboViewModel extends ViewModel
{
    /**
     * Extends Spatie's ViewModel system to automagically add the
     * get<Name>Attribute methods if they're protected, and map them
     * to a snake case variant.
     *
     * @return Collection
     */
    protected function items(): Collection
    {
        // Get reflection class
        $class = new ReflectionClass($this);

        // Check all protected methods
        $protectedMethods = collect($class->getMethods(ReflectionMethod::IS_PROTECTED))
            ->filter(function (ReflectionMethod $method) {
                // Must meet get<Name>Attribute
                return preg_match('/^get([A-Z][a-zA-Z0-9]+)Attribute$/', $method->getName());
            })
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
