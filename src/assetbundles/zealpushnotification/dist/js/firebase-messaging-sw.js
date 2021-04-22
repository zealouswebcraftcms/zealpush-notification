importScripts("https://www.gstatic.com/firebasejs/8.2.4/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.2.4/firebase-messaging.js");

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
    apiKey: "{api_key}",
    authDomain: "{auth_domain}",
    projectId: "{project_id}",
    storageBucket: "{storage_bucket}",
    messagingSenderId: "{messaging_sender_id}",
    appId: "{app_id}",
});


// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
    
    // Customize notification here
    const notificationTitle = payload.data.title;;
    const notificationOptions = {
        body: payload.data.body,
        icon: payload.data.icon,
        image: payload.data.image,
        click_action: payload.data.click_action,
        data: {
            click_action: payload.data.click_action,
            image: payload.data.image,
        }  
    };  

    self.addEventListener('notificationclick', function(payload) {

        if (!payload.action) {
            // Was a normal notification click
            self.clients.openWindow(payload.notification.data.click_action, '_blank')
            payload.notification.close();
            return;
        }else{
            payload.notification.close();
        }
    });

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});

messaging.onBackgroundMessage(function(payload) {
    
    // Customize notification here
     const notificationTitle = payload.data.title;;
    const notificationOptions = {
        body: payload.data.body,
        icon: payload.data.icon,
        image: payload.data.image,
        click_action: payload.data.click_action,
        data: {
            click_action: payload.data.click_action,
            image: payload.data.image,
        }   
    };  

    self.addEventListener('notificationclick', function(payload) {

        if (!payload.action) {
            // Was a normal notification click
            self.clients.openWindow(payload.notification.data.click_action, '_blank')
            payload.notification.close();
            return;
        }else{
            payload.notification.close();
        }
    });

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});