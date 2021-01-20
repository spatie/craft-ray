<?php
/**
 * ray plugin for Craft CMS 3.x
 *
 * Easily debug CraftCMS projects
 *
 * @link      https://spatie.be
 * @copyright Copyright (c) 2021 Spatie
 */

namespace Spatie\CraftRay\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

/**
 * @author    Spatie
 * @package   Ray
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /** @var bool */
    public $enable = true;

    /** @var string */
    public $host = 'localhost';

    /** @var int */
    public $port = 23517;

    /** @var ?string */
    public $remote_path = null;

    /** @var ?string */
    public $local_path = null;

    public function behaviors()
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['enable', 'host', 'port', 'remote_path', 'local_path'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['host', 'port'], 'required'],
            ['enable', 'boolean'],
        ];
    }
}
