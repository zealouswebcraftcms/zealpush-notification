<?php
/**
 * Zealpush Notification plugin for Craft CMS 3.x
 *
 * Zealpush Notification
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 ZealousWeb
 */

namespace zealouswebcraftcms\zealpushnotification\services;

use zealouswebcraftcms\zealpushnotification\ZealpushNotification;

use Craft;
use craft\base\Component;

/**
 * ZealpushNotificationService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    ZealousWeb
 * @package   ZealpushNotification
 * @since     1.0.0
 */
class ZealpushNotificationService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     ZealpushNotification::$plugin->zealpushNotificationService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (ZealpushNotification::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
