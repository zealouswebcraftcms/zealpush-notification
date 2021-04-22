<?php
/**
 * Zealpush Notification plugin for Craft CMS 3.x
 *
 * Zealpush Notification
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 ZealousWeb
 */

namespace zealouswebcraftcms\zealpushnotification\models;

use zealouswebcraftcms\zealpushnotification\ZealpushNotification;

use Craft;
use craft\base\Model;

/**
 * ZealpushNotification Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    ZealousWeb
 * @package   ZealpushNotification
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $apiKey;
    public $authDomain;
    public $messagingSenderId;
    public $appId;
    public $projectId;
    public $storageBucket;
    public $serverKey;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [        
            [['apiKey'], 'required','message' => 'Api key can not be blank'],
            [['authDomain'], 'required','message' => ' Auth Domain can not be blank'],
            [['messagingSenderId'], 'required','message' => ' Messaging Sender Id can not be blank'],
            [['appId'], 'required','message' => 'App Id can not be blank'],
            [['projectId'], 'required','message' => 'Project Id can not be blank'],
            [['storageBucket'], 'required','message' => 'Storage Bucket can not be blank'],
            [['serverKey'], 'required','message' => 'Server Key can not be blank'],
        ];
    }
}
