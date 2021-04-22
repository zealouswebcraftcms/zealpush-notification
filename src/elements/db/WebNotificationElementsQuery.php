<?php

namespace zealouswebcraftcms\zealpushnotification\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class WebNotificationElementsQuery extends ElementQuery
{
    public $notificationsTitle;
    public $notificationsText;
    public $notificationsIcon;
    public $notificationsUrl;
    public $notificationsBannerImage;
    public $notificationsStatus;
    public $dateCreated;

    public function notificationsTitle($value)
    {
        $this->notificationsTitle = $value;

        return $this;
    }

    public function notificationsText($value)
    {
        $this->notificationsText = $value;

        return $this;
    }

    public function notificationsIcon($value)
    {
        $this->notificationsIcon = $value;

        return $this;
    }

    public function notificationsUrl($value)
    {
        $this->notificationsUrl = $value;

        return $this;
    }

    public function notificationsBannerImage($value)
    {
        $this->notificationsBannerImage = $value;

        return $this;
    }
    public function notificationsStatus($value)
    {
        $this->notificationsStatus = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('user_notifications');

        // select the columns
        $this->query->select([
            'user_notifications.notificationsTitle',
            'user_notifications.notificationsText',
            'user_notifications.notificationsIcon',
            'user_notifications.notificationsUrl',
            'user_notifications.notificationsBannerImage',
            'user_notifications.notificationsStatus',
            'user_notifications.dateCreated',
        ]);

        if ($this->notificationsTitle) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsTitle', $this->notificationsTitle));
        }

        if ($this->notificationsText) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsText', $this->notificationsText));
        }

        if ($this->notificationsIcon) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsIcon', $this->notificationsIcon));
        }

        if ($this->notificationsUrl) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsUrl', $this->notificationsUrl));
        }

        if ($this->notificationsBannerImage) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsBannerImage', $this->notificationsBannerImage));
        }

        if ($this->notificationsStatus) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.notificationsStatus', $this->notificationsStatus));
        }

        if ($this->dateCreated) {
            $this->subQuery->andWhere(Db::parseParam('user_notifications.dateCreated', $this->dateCreated));
        }

        return parent::beforePrepare();
    }
}
