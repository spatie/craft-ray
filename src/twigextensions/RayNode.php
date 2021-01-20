<?php

namespace Spatie\CraftRay\twigextensions;

use Twig\Compiler;
use Twig\Node\Node;

class RayNode extends Node
{
    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('ray(')
            ->subcompile($this->getNode('var'))
            ->raw(");\n");
    }
}
