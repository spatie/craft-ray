<?php
/**
 * ray plugin for Craft CMS 3.x
 *
 * Easily debug CraftCMS projects
 *
 * @link      https://spatie.be
 * @copyright Copyright (c) 2021 Spatie
 */

namespace Spatie\CraftRay;

use Spatie\CraftRay\twigextensions\RayTwigExtension;
use Spatie\CraftRay\models\Settings;

use Craft;
use craft\base\Plugin;

use Spatie\Ray\Client;
use Spatie\Ray\Payloads\Payload;
use Yii;

/**
 * Class Ray
 *
 * @author    Spatie
 * @package   Ray
 * @since     1.0.0
 *
 */
class CraftRay extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CraftRay
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new RayTwigExtension());

        Yii::$container->set(Ray::class, function () {
            $client = Yii::$container->get(Client::class);
            $craftRaySettings = CraftRay::getInstance()->getSettings();
            $settings = new \Spatie\Ray\Settings\Settings([
                'enable' => $craftRaySettings->enable,
                'host' => $craftRaySettings->host,
                'port' => $craftRaySettings->port,
                'remote_path' => $craftRaySettings->remote_path,
                'local_path' => $craftRaySettings->local_path,
            ]);

            $ray = new Ray($settings, $client);

            if (! $settings->enable) {
                $ray->disable();
            }

            return $ray;
        });

        Payload::$originFactoryClass = OriginFactory::class;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-ray/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
