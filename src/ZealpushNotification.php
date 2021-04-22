<?php
/**
 * Zealpush Notification plugin for Craft CMS 3.x
 *
 * Zealpush Notification
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 ZealousWeb
 */

namespace zealouswebcraftcms\zealpushnotification;

use zealouswebcraftcms\zealpushnotification\services\ZealpushNotificationService as ZealpushNotificationServiceService;
use zealouswebcraftcms\zealpushnotification\variables\ZealpushNotificationVariable;
use zealouswebcraftcms\zealpushnotification\models\Settings;
use zealouswebcraftcms\zealpushnotification\fields\ZealpushNotificationField as ZealpushNotificationFieldField;
use zealouswebcraftcms\zealpushnotification\records\ZealpushNotificationRecord;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\services\Utilities;
use craft\helpers\UrlHelper;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    ZealousWeb
 * @package   ZealpushNotification
 * @since     1.0.0
 *
 * @property  ZealpushNotificationServiceService $zealpushNotificationService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class ZealpushNotification extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * ZealpushNotification::$plugin
     *
     * @var ZealpushNotification
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * ZealpushNotification::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->hook('get-webnotification-token', function(array &   $context, &$template) {
            $settings = $this->getSettings();
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $template = \Craft::$app->view->renderTemplate('zealpush-notification/token', [
                        'settings' => $settings,
                    ]);
            Craft::$app->view->setTemplateMode($oldMode);
            return $template;
        });

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'zealpush-notification/default';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'zealpush-notification/default/do-something';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                // $event->rules['zealpush-notification'] = 'zealpush-notification/default/notification-data';
                $event->rules['cpActionTrigger1'] = 'zealpush-notification/default/do-something';
            }
        );
       
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['get-notification-data-for-edit/<submissionId:\d+>'] = 'zealpush-notification/default/get-notification-data-for-edit';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['save-notification-edit-data'] = 'zealpush-notification/default/save-notification-edit-data';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['get-notification-data-for-save'] = 'zealpush-notification/default/get-notification-data-for-save';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['send-notification-action'] = 'zealpush-notification/default/send-notification-action';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ZealpushNotificationFieldField::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('zealpushNotification', ZealpushNotificationVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        /**
        * Logging in Craft involves using one of the following methods:
        *
        * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
        * Craft::info(): record a message that conveys some useful information.
        * Craft::warning(): record a warning message that indicates something unexpected has happened.
        * Craft::error(): record a fatal error that should be investigated as soon as possible.
        *
        * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
        *
        * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
        * the category to the method (prefixed with the fully qualified class name) where the constant appears.
        *
        * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
        * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
        *
        * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
        */
        Craft::info(
            Craft::t(
                'zealpush-notification',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        $vendor_path = Craft::getAlias('@vendor'); 
        $dir_path = dirname($vendor_path);
        
        Craft::setAlias('@banner-image-alias', $dir_path.'/vendor/zealouswebcraftcms/zealpush-notification/resources/banner_images'); 
        Craft::setAlias('@icon-image-alias', $dir_path.'/vendor/zealouswebcraftcms/zealpush-notification/resources/icon_images'); 

        Craft::setAlias('@base-path', CRAFT_BASE_PATH); 

        $web_path = Craft::getAlias('@web');
        $test = explode("/",$web_path);
        array_pop($test);
        $base_path_url = implode('/',$test);
        Craft::setAlias('@base-url-path', $base_path_url); 
        Craft::setAlias('@banner-base-url', $base_path_url.'/vendor/zealouswebcraftcms/zealpush-notification/resources/banner_images'); 
        Craft::setAlias('@icon-base-url', $base_path_url.'/vendor/zealouswebcraftcms/zealpush-notification/resources/icon_images'); 
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $webrootpath = Craft::getAlias('@web'); 
        $current_site_path = dirname($webrootpath);
        return Craft::$app->view->renderTemplate(
            'zealpush-notification/settings',
            [
                'settings' => $this->getSettings(),
                'current_site_path' => $current_site_path
            ]
        );
    }

    public function afterSaveSettings()
    {
        parent::afterSaveSettings();
        Craft::$app->response
            ->redirect(UrlHelper::url('settings/plugins/zealpush-notification'))
            ->send();
    } 

    public function getCpNavItem()
    {
        if(!$this->getSettings()->apiKey) {
            return;
        }
                
        $navItem = parent::getCpNavItem();

        $navItem['label'] = Craft::t('zealpush-notification', 'Zealpush Notificattion');

        return $navItem;
    }
}
