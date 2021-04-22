<?php
/**
 * Zealpush Notification plugin for Craft CMS 3.x
 *
 * Zealpush Notification
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 ZealousWeb
 */

namespace zealouswebcraftcms\zealpushnotification\migrations;

use zealouswebcraftcms\zealpushnotification\ZealpushNotification;
use zealouswebcraftcms\zealpushnotification\elements\WebNotificationElements;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\volumes\Local;
use yii\base\Exception;
use craft\services\Volumes;
use craft\helpers\Db;
use craft\db\Table;

/**
 * Zealpush Notification Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    ZealousWeb
 * @package   ZealpushNotification
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }


        $test = explode("/",Craft::getAlias('@web'));
        array_pop($test);
        $base_path_url = implode('/',$test);
        
        $ICON_URL = $base_path_url.'/vendor/zealouswebcraftcms/zealpush-notification/resources/icon_images';
        $ICON_PATH = dirname(Craft::getAlias('@vendor')).'/vendor/zealouswebcraftcms/zealpush-notification/resources/icon_images';

        $icon_volume = new Local([
            'name' => 'ZealIconImage',
            'handle' => 'zealiconimage',
            'hasUrls' => true,
            'url' => $ICON_URL,
            'path' => $ICON_PATH,
        ]);
        
        if (!Craft::$app->volumes->saveVolume(($icon_volume))) {
            throw new Exception('Couldn’t save icon volume.');
        }

        $BANNER_URL = $base_path_url.'/vendor/zealouswebcraftcms/zealpush-notification/resources/banner_images';
        $BANNER_PATH = dirname(Craft::getAlias('@vendor')).'/vendor/zealouswebcraftcms/zealpush-notification/resources/banner_images';

        $banner_volume = new Local([
            'name' => 'ZealBannerImage',
            'handle' => 'zealbannerimage',
            'hasUrls' => true,
            'url' => $BANNER_URL,
            'path' => $BANNER_PATH,
        ]);
        
        if (!Craft::$app->volumes->saveVolume(($banner_volume))) {
            throw new Exception('Couldn’t save banner image volume.');
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();
        $this->deleteElementData();

        $zealiconimage = \Craft::$app->getVolumes()->getVolumeByHandle('zealiconimage');
        $zealbannerimage = \Craft::$app->getVolumes()->getVolumeByHandle('zealbannerimage');

        if($zealiconimage) {
            Db::delete(Table::VOLUMES, [
                    'id' => $zealiconimage->id,
                ]);
        }
        if($zealbannerimage) {
            Db::delete(Table::VOLUMES, [
                    'id' => $zealbannerimage->id,
                ]);
        }
        
        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $usertableSchema = Craft::$app->db->schema->getTableSchema('{{%user_notifications}}');
        if ($usertableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%user_notifications}}',
                [
                    'id' => $this->primaryKey()->notNull(),
                    'notificationsTitle' => $this->string()->notNull(),
                    'notificationsText' => $this->text()->notNull(),
                    'notificationsIcon' => $this->integer()->null(),
                    'notificationsUrl'  => $this->string()->null(),                    
                    'notificationsBannerImage' => $this->integer()->null(),    
                    'notificationsStatus' => $this->string()->defaultvalue('Active')->null(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        $tokentableSchema = Craft::$app->db->schema->getTableSchema('{{%user_notifications_token}}');
        if ($tokentableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%user_notifications_token}}',
                [
                    'id' => $this->primaryKey()->notNull(),
                    'notificationsToken' => $this->string()->null(),                    
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'dateDeleted' => $this->dateTime()->null(),
                    'uid' => $this->uid(),
                ]
            );
        }
        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%user_notifications}}', 'id'),
            '{{%user_notifications}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%user_notifications}}', 'notificationsIcon'),
            '{{%user_notifications}}',
            'id',
            '{{%assets}}',
            'id',
            'SET NULL',
            null
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%user_notifications}}', 'notificationsBannerImage'),
            '{{%user_notifications}}',
            'id',
            '{{%assets}}',
            'id',
            'SET NULL',
            null
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%user_notifications}}');
        $this->dropTableIfExists('{{%user_notifications_token}}');
    }

    /**
     * Delete existing notification data.
     */
    protected function deleteElementData()
    {
        // Delete notification elements
        $this->delete(
            '{{%elements}}',
            ['type' => WebNotificationElements::class]
        );
    }
}
