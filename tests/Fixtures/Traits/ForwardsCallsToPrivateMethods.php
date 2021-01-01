<?php

namespace Tests\Fixtures\Traits;

use Error;
use LogicException;

trait ForwardsCallsToPrivateMethods
{
    public function __call($name, $args)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$args);
        }

        try {
            return parent::__call($name, $args);
        } catch (Error $error) {
            dd($error->getMessage());

            $self = static::class;
            throw new LogicException("Method {$name} not found on {$self}");
        }
    }
}
