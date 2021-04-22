<?php
namespace zealouswebcraftcms\zealpushnotification\elements;

use Craft;
use craft\base\Element;
use zealouswebcraftcms\zealpushnotification\elements\db\WebNotificationElementsQuery;
use craft\elements\db\ElementQueryInterface;
use zealouswebcraftcms\zealpushnotification\elements\actions\ReSendNotification;
use craft\elements\actions\Delete;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\web\View;
use craft\elements\Asset;
use craft\i18n\Locale;

/**
 *  Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    zealousweb
 
 * @since     1.0.0
 */
class WebNotificationElements extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    public $notificationsTitle;
    public $notificationsText;
    public $notificationsIcon;
    public $notificationsUrl;
    public $notificationsBannerImage;
    public $notificationsStatus;
    public $dateCreated;

    // Static Methods
    // =========================================================================

    public static function hasContent(): bool
    {
        return true;
    }

    public static function isLocalized(): bool
    {
        return false;
    }

    public static function find(): ElementQueryInterface
    {
        return new WebNotificationElementsQuery(static::class);
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['notificationsTitle', 'notificationsText', 'notificationsUrl', 'notificationsStatus'];
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('get-notification-data-for-edit/'.$this->id);
    }

    protected static function defineSources(string $context = null): array
    {
        /* $forms = array_unique(array_map(function (self $submission) {
            return $submission->form;
        }, self::find()->all())); */

        $sources = [
            [
                'key'      => '*',
                'label'    => Craft::t('zealpush-notification', 'All Notifications'),
                'criteria' => [],
            ],
        ];

        /* foreach ($forms as $formHandle) {
            $sources[] = [
                'key'      => $formHandle,
                'label'    => ucfirst($formHandle),
                'criteria' => ['form' => $formHandle],
            ];
        } */

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        /* Delete */
        $actions[] = Delete::class;

        /* Resend Notification */
        $actions[] = ReSendNotification::class;
       
        return $actions;
    }

    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'id'          => Craft::t('zealpush-notification', 'ID'),
            'notificationsTitle'          => Craft::t('zealpush-notification', 'Title'),
            'notificationsText'        => Craft::t('zealpush-notification', 'Text'),
            'notificationsUrl'    => Craft::t('zealpush-notification', 'URL'),
            'notificationsIcon'     => Craft::t('zealpush-notification', 'Icon'),
            'notificationsBannerImage'   => Craft::t('zealpush-notification', 'Banner Image'),
            'notificationsStatus'     => Craft::t('zealpush-notification', 'Status'),
            'dateCreated' => Craft::t('zealpush-notification', 'Date Created'),
        ];
                                           
        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'id',
            'notificationsTitle',
            'notificationsText',
            'notificationsUrl',
            'notificationsIcon',
            'notificationsBannerImage',
            'notificationsStatus',
            'dateCreated',
        ];
    }

    public function getTableAttributeHtml(string $attribute): string
    {
        if ($attribute == 'notificationsTitle') {
            $html = '<a href="'.UrlHelper::cpUrl('get-notification-data-for-edit/'.$this->id).'">'. $this->notificationsTitle .'</a>'; 
            return StringHelper::convertToUtf8($html);
        }

        if ($attribute == 'notificationsText') {
            $data = $this->notificationsText;
            $data_length = strlen($data);
            if($data_length > 20) {
                $html = \Craft::$app->view->renderTemplate('zealpush-notification/notificationText', [
                            'id' => $this->id,
                            'notificationsText' => $data,
                        ]);
            } else {
                $html = $data;
            }
            return StringHelper::convertToUtf8($html);
        }
        if ($attribute == 'notificationsIcon') {
            if($this->notificationsIcon) {
                $icon_assets = Asset::findOne($this->notificationsIcon);
                if(!empty($icon_assets)) {
                    $html = \Craft::$app->view->renderTemplate('zealpush-notification/listingImage', [
                        'icon_assets' => [$icon_assets],
                        'elementType' => Asset::class 
                    ]);
                } else {
                    $html = '';
                }
            } else {
                $html = '';
            }
            return StringHelper::convertToUtf8($html);
        }

        if ($attribute == 'notificationsBannerImage') {
            if($this->notificationsBannerImage) {
                $banner_assets = Asset::findOne($this->notificationsBannerImage);
                if(!empty($banner_assets)) {
                    $html = \Craft::$app->view->renderTemplate('zealpush-notification/listingImage', [
                        'banner_assets' => [$banner_assets],
                        'elementType' => Asset::class 
                    ]);
                } else {
                    $html = '';
                }
            } else {
                $html = '';
            }    
            return StringHelper::convertToUtf8($html);
        }

        if ($attribute == 'dateCreated') {
            $html = Craft::$app->getFormatter()->asDate($this->dateCreated, Locale::LENGTH_SHORT); 
            return StringHelper::convertToUtf8($html);
        }
        return parent::getTableAttributeHtml($attribute); // TODO: Change the autogenerated stub
    }

    protected static function defineSortOptions(): array
    {
        // $sortOptions = parent::defineSortOptions();
        $sortOptions = [
            'id'          => Craft::t('zealpush-notification', 'ID'),
            'notificationsTitle'          => Craft::t('zealpush-notification', 'Title'),
            'notificationsUrl'    => Craft::t('zealpush-notification', 'URL'),
            'notificationsStatus'     => Craft::t('zealpush-notification', 'Status'),
            'dateCreated' => Craft::t('zealpush-notification', 'Date Created'),
        ];

        return $sortOptions;
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('app', 'Zealpush Notification');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('app', 'Zealpush notification');
    }
}
