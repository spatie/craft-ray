<?php

namespace Spatie\CraftRay\twigextensions;

class RayVariable
{
    public function __call($name, $arguments)
    {
        if (! count($arguments)) {
            return ray()->$name();
        }

        return ray()->$name($arguments);
    }
}
