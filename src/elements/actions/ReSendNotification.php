<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace zealouswebcraftcms\zealpushnotification\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use yii\base\Exception;
use zealouswebcraftcms\zealpushnotification\elements\WebNotificationElements;
use zealouswebcraftcms\zealpushnotification\controllers\DefaultController;

/**
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class ReSendNotification extends ElementAction
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get resend
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get resend
     */
    public $successMessage;

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Resend Notification');
    }

    /** 
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        if ($this->confirmationMessage !== null) {
            return $this->confirmationMessage;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();
        
        try {
            $flag = "resend-notification";
            $failed_array = [];
            $active_ids = [];
            foreach ($query->all() as $data) {
                if($data['notificationsStatus'] == 'Active') {
                    DefaultController::actionSendNotification($data, $flag);
                    $active_ids[] = $data['id'];
                } else {
                    $failed_array[] = $data['id'];
                }
            }

            if(count($failed_array) > 0) {
                if(count($failed_array) == 1 && count($active_ids) == 0) {
                    $this->setMessage(Craft::t('app', 'You need to "Active" the stauts to resend notification.'));
                } else {
                    $this->setMessage(Craft::t('app', 'All notification has been sent successfully except for this IDs ('. implode(",", $failed_array).'). You need to "Active" the stauts to resend notification.'));
                }
            } else {
                if(count($active_ids) == 1) {
                    $this->setMessage(Craft::t('app', 'Notification has been sent successfully.'));
                } else {
                    $this->setMessage(Craft::t('app', 'All notification has been sent successfully.'));
                }
            }
        } catch (Exception $exception) {
            $this->setMessage($exception->getMessage());
            return false;
        }
        
        return true;

    }

}
