<?php

namespace Spatie\CraftRay;

use Composer\InstalledVersions;
use Craft;
use Spatie\Ray\Ray as BaseRay;
use Spatie\YiiRay\Ray as YiiRay;

class Ray extends YiiRay
{
    public function __toString(): string
    {
        return '';
    }

    /**
     * @param \Spatie\Ray\Payloads\Payload|\Spatie\Ray\Payloads\Payload[] $payloads
     * @param array $meta
     *
     * @return \Spatie\Ray\Ray
     * @throws \Exception
     */
    public function sendRequest($payloads, array $meta = []): BaseRay
    {
        if (! $this->enabled()) {
            return $this;
        }

        $meta = [
            'craft_version' => Craft::getVersion(),
        ];

        if (class_exists(InstalledVersions::class)) {
            $meta['craft_ray_package_version'] = InstalledVersions::getVersion('spatie/craft-ray');
        }

        return BaseRay::sendRequest($payloads, $meta);
    }
}
