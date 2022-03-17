<?php
/**
 * ray plugin for Craft CMS 4.x
 *
 * Easily debug CraftCMS projects
 *
 * @link      https://spatie.be
 * @copyright Copyright (c) 2022 Spatie
 */

namespace Spatie\CraftRay;

use Craft;
use craft\base\Plugin;

use Spatie\CraftRay\models\Settings;
use Spatie\CraftRay\twigextensions\RayTwigExtension;

use Spatie\Ray\Payloads\Payload;
use Yii;

class CraftRay extends Plugin
{
    public static $plugin;

    public bool $hasCpSettings = true;

    public bool $hasCpSection = false;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new RayTwigExtension());

        Yii::$container->set(Ray::class, function () {
            $craftRaySettings = CraftRay::getInstance()->getSettings();
            $settings = new \Spatie\Ray\Settings\Settings([
                'enable' => $craftRaySettings->enable,
                'host' => $craftRaySettings->host,
                'port' => $craftRaySettings->port,
                'remote_path' => $craftRaySettings->remote_path,
                'local_path' => $craftRaySettings->local_path,
            ]);

            $ray = new Ray($settings);

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
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'craft-ray/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
