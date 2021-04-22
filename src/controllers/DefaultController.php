<?php
/**
 * Zealpush Notification plugin for Craft CMS 3.x
 *
 * Zealpush Notification
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 ZealousWeb
 */

namespace zealouswebcraftcms\zealpushnotification\controllers;

use zealouswebcraftcms\zealpushnotification\ZealpushNotification;
use zealouswebcraftcms\zealpushnotification\models\UserNotificationsModel;
use zealouswebcraftcms\zealpushnotification\models\ZealpushNotificationModel;
use zealouswebcraftcms\zealpushnotification\records\ZealpushNotificationRecord;
use zealouswebcraftcms\zealpushnotification\records\WebNotificationsToken;

use Craft;
use craft\web\Controller;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
use yii\base\Event;
use craft\base\Element;
use yii\base\Model;
use craft\base\ElementInterface;
use craft\web\UploadedFile;
use craft\helpers\UrlHelper;
use craft\web\AssetBundle;
use craft\events\RegisterElementSortOptionsEvent;
use craft\elements\db\ElementQueryInterface;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\RegisterElementDefaultTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use zealouswebcraftcms\zealpushnotification\elements\WebNotificationElements;
use zealouswebcraftcms\zealpushnotification\fields\ZealpushNotificationField;
use craft\elements\Asset;
use Yii;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    ZealousWeb
 * @package   ZealpushNotification
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /* Call Add page */
    public function actionGetNotificationDataForSave() {
        $submission = new UserNotificationsModel();  
        return $this->renderTemplate('zealpush-notification/add', [
            'elementType' => Asset::class,
            'record' => $submission,
            'icon_assets' => [],
            'banner_assets' => [],
        ]);
    } 

    /* save added notification */
    public function actionSaveEntry(){

        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $elementsService = Craft::$app->getElements();

        $submission = new UserNotificationsModel();
        
        $submission->notificationsTitle = $request->getBodyParam('notificationsTitle');
        $submission->notificationsText = $request->getBodyParam('notificationsText');
        $submission->notificationsIcon = $request->getBodyParam('notificationsIcon');
        $submission->notificationsUrl = $request->getBodyParam('notificationsUrl');
        $submission->notificationsStatus = $request->getBodyParam('notificationsStatus');
        $submission->notificationsBannerImage = $request->getBodyParam('notificationsBannerImage');
        
        if ($submission->validate()) {
            $record = new ZealpushNotificationRecord;
            $record->notificationsTitle = $submission->notificationsTitle;
            $record->notificationsText = $submission->notificationsText;
            $record->notificationsUrl = $submission->notificationsUrl;
            $record->notificationsStatus = $submission->notificationsStatus;
            $record->notificationsIcon = ($submission->notificationsIcon) ? $submission->notificationsIcon[0] : NULL;
            $record->notificationsBannerImage = ($submission->notificationsBannerImage) ? $submission->notificationsBannerImage[0] : NULL;
            
            $data = new WebNotificationElements;
            $data->setScenario(Element::SCENARIO_LIVE);
            $success = $elementsService->saveElement($data);
            $record->id = $data->id;           
            if ($record->save())
            {
                if($record->notificationsStatus == 'Active') {
                    $flag = '';
                    $this->actionSendNotification($submission, $flag);
                }
                return $this->redirect('zealpush-notification');
            }
        } 
        return $this->renderTemplate('zealpush-notification/add', [
            'elementType' => Asset::class,
            'record' => $submission,
            'icon_assets' => [Asset::findOne($submission->notificationsIcon)],
            'banner_assets' => [Asset::findOne($submission->notificationsBannerImage)],
        ]);
    }

    /* call edit page */
    public function actionGetNotificationDataForEdit(string $submissionId) {
        $record = ZealpushNotificationRecord::findOne($submissionId); 
        $icon_assets = [];
        $banner_assets = [];
        if($record->notificationsIcon) {
            $icon_assets = [Asset::findOne($record->notificationsIcon)];
        }
        if($record->notificationsBannerImage) {
            $banner_assets = [Asset::findOne($record->notificationsBannerImage)];
        }
        return $this->renderTemplate('zealpush-notification/edit',[
            'record' =>	$record,
            'icon_assets' => $icon_assets,
            'banner_assets' => $banner_assets,
            'elementType' => Asset::class              
        ]);
    }

    /* save edited notification */
    public function actionSaveNotificationEditData(){
        
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $elementsService = Craft::$app->getElements();
        $id = $request->getBodyParam('recordId');
        $submission = new UserNotificationsModel();
        $submission->id = $request->getBodyParam('recordId');
        $submission->notificationsTitle = $request->getBodyParam('notificationsTitle');
        $submission->notificationsText = $request->getBodyParam('notificationsText');
        $submission->notificationsIcon = $request->getBodyParam('notificationsIcon');
        $submission->notificationsUrl = $request->getBodyParam('notificationsUrl');
        $submission->notificationsStatus = $request->getBodyParam('notificationsStatus');
        $submission->notificationsBannerImage = $request->getBodyParam('notificationsBannerImage');
        if ($submission->validate()) {
            $record = ZealpushNotificationRecord::findOne($id);
            $record->notificationsTitle = $submission->notificationsTitle;
            $record->notificationsText = $submission->notificationsText;            
            $record->notificationsUrl = $submission->notificationsUrl;
            $record->notificationsStatus = $submission->notificationsStatus;
            $record->notificationsIcon = ($submission->notificationsIcon) ? $submission->notificationsIcon[0] : NULL;
            $record->notificationsBannerImage = ($submission->notificationsBannerImage) ? $submission->notificationsBannerImage[0] : NULL;
            
            $data = WebNotificationElements::findOne($id);
            $data->setScenario(Element::SCENARIO_LIVE);
            $success = $elementsService->saveElement($data);
            if ($record->save())
            {
                if($record->notificationsStatus == 'Active') {
                    $flag = '';
                    $this->actionSendNotification($submission, $flag);
                }
                return $this->redirect('zealpush-notification');
            }
        } 
        return $this->renderTemplate('zealpush-notification/edit', [
            'elementType' => Asset::class,
            'record' => $submission,
            'icon_assets' => [Asset::findOne($submission->notificationsIcon)],
            'banner_assets' => [Asset::findOne($submission->notificationsBannerImage)],
        ]);
    }

    /* save browser token */
    public function actionSaveToken()
    { 
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $submission = new ZealpushNotificationModel();
        $dataArr = [];
        $submission->notificationsToken = $request->getBodyParam('token');
        if ($submission->validate()) {
            $record = new WebNotificationsToken;
            $record->notificationsToken =  $request->getBodyParam('token');
          
            if($record->save()) {
                $dataArr = [
                    'message'=>'success',
                    'message_text' => 'Token has been save successfully.'                
                ];
            } 
            else {
                $dataArr = [
                    'message'=>'error', 
                    'message_text'=>'please try again',                
                ];
            }
        }
        return json_encode($dataArr);
    }

    /* Send notification function */
    public function actionSendNotification($submission, $flag) 
    {
        $icon_assets = NULL;
        $banner_assets = NULL;
        
        if(isset($submission->notificationsIcon)) {
            $icon_assets = Asset::findOne($submission->notificationsIcon);
        }
        if(isset($submission->notificationsBannerImage)) {
            $banner_assets = Asset::findOne($submission->notificationsBannerImage);
        }
        
        $get_browser_tokens = WebNotificationsToken::find()->all();
        $browser_tokens = array();
        foreach($get_browser_tokens as $res)  {
            $browser_tokens[] = trim($res['notificationsToken']);
        }

        $ch = curl_init();
        $default_site_url = Yii::getAlias('@web');
        $msg = array(
            'title' => $submission->notificationsTitle,
            'body' => $submission->notificationsText,
            'click_action' => $submission->notificationsUrl ? $submission->notificationsUrl : $default_site_url,
            'icon' => ($icon_assets) ? trim($icon_assets->url) : NULL,
            'image' => ($banner_assets) ? trim($banner_assets->url) : NULL,
        );
       
        $payload = array(
            'registration_ids' => $browser_tokens,
            'data' => $msg,
            "priority" => "high",
        );

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $headers = array();
        $plugin = ZealpushNotification::getInstance();
        $settings = $plugin->getSettings();
        $headers[] = 'Authorization: key='.trim($settings->serverKey);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        if($flag != 'resend-notification') {
            $this->setSuccessFlash(Craft::t('app', 'Notification send successfully.'));
        }
    }
}
