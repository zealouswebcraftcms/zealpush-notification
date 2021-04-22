/**
 * Zealpush Notification plugin for Craft CMS
 *
 * Zealpush Notification JS
 *
 * @author    ZealousWeb
 * @copyright Copyright (c) 2021 ZealousWeb
 * @link      https://www.zealousweb.com
 * @package   ZealpushNotification
 * @since     1.0.0
 */
  
$(document).ready(function() {
    
    /* Listing page in display whole text in modal */
        $(".notificationTextMessage").on("click", function() {
            var text_message = $(".notificationTextMessage").attr('rel-val');
            $('.notificationTextFullMessage').text(text_message);
            $("#notificationTextModal").show();
            $(".elementselect").hide();
        });

        $("#hideNotificationModal").on("click", function() {
            $("#notificationTextModal").hide()
            $(".elementselect").show();
        }); 
   
});
 
 