<?php
/**
 * ray plugin for Craft CMS 3.x
 *
 * Easily debug CraftCMS projects
 *
 * @link      https://spatie.be
 * @copyright Copyright (c) 2021 Spatie
 */

namespace spatiecraftray\raytests\unit;

use Codeception\Test\Unit;
use Craft;
use spatiecraftray\ray\Ray;
use UnitTester;

/**
 * ExampleUnitTest
 *
 *
 * @author    Spatie
 * @package   Ray
 * @since     1.0.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Ray::class,
            Ray::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
