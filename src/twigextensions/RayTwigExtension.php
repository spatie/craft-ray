<?php
/**
 * ray plugin for Craft CMS 3.x
 *
 * Easily debug CraftCMS projects
 *
 * @link      https://spatie.be
 * @copyright Copyright (c) 2021 Spatie
 */

namespace Spatie\CraftRay\twigextensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author    Spatie
 * @package   Ray
 * @since     1.0.0
 */
class RayTwigExtension extends AbstractExtension implements GlobalsInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Ray';
    }

    public function getTokenParsers()
    {
        return [
            new RayTokenParser(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter('ray', function ($params) {
                return ray($params);
            }),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('ray', function ($params) {
                return ray($params);
            }),
        ];
    }

    public function getGlobals()
    {
        return [
            'ray' => new RayVariable,
        ];
    }
}
