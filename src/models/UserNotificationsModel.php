<?php
/**
 * Web Notifications  plugin for Craft CMS 3.x
 *
 * Web notifications are a permission-based marketing channel. Before receiving a web push, users have to opt in to receive them. The opt-in prompt comes from the user's web browser.


 *
 * @link      Zealousweb.com
 * @copyright Copyright (c) 2021 Zealousweb
 */

namespace zealouswebcraftcms\zealpushnotification\models;

use zealouswebcraftcms\zealpushnotification\ZealpushNotification;

use Craft;
use craft\base\Model;
use craft\elements\Asset;

/**
 * ZealpushNotificationModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Zealousweb
 * @package   ZealpushNotification
 * @since     1.0.0
 */
class UserNotificationsModel extends Model
{
    // Public Properties
    // =========================================================================
    public $file;
    /**
     * Some model attribute
     *
     * @var string
     */
    public $id;
    public $notificationsTitle;
    public $notificationsText;
    public $notificationsIcon;
    public $notificationsUrl;
    public $notificationsBannerImage;
    public $notificationsStatus;

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
            [['notificationsTitle'], 'required','message' => 'Notification title can not be blank.'],
            [['notificationsTitle'], 'customValidationTitle'],
            [['notificationsText'], 'required','message' => 'Notification text can not be blank.'],         
            [['notificationsText'], 'customValidationText'],               
            [['notificationsIcon'], 'customValidationImage'],               
            [['notificationsBannerImage'], 'customValidationImage'],               
            [['notificationsUrl'], 'url', 'message' => 'Please enter valid url.'],   
        ];
    }

    public function attributeLabels() {
        return [
            'notificationsTitle' => 'Title',
            'notificationsText' => 'Text'         
        ];
    }

    public function customValidationTitle($attribute)
    {
      if(strlen(trim($this->$attribute)) > 255)
        $this->addError($attribute, 'You have reached maximum limit of Title');
    }
    
    public function customValidationText($attribute)
    {
      if(strlen(trim($this->$attribute)) > 255)
        $this->addError($attribute, 'You have reached maximum limit of Text');
    }

    public function customValidationImage($attribute)
    {
        if(!in_array(strtolower($this->getExtension($attribute)), ['jpg', 'png', 'gif', 'webp', 'tiff', 'psd', 'raw', 'bmp', 'heif', 'indd', 'jpeg', 'svg', 'ai', 'eps'], true)) {
            $this->addError($attribute, 'Please upload image only');
        }
    }

    /**
     * Returns the file extension.
     *
     * @return string
     */
    public function getExtension($attribute): string
    {
        $assets = Asset::findOne($this->$attribute);
        return pathinfo($assets->url, PATHINFO_EXTENSION);
    }
}
