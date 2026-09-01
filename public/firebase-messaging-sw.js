importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyCKgmPCZHgTrfy5uU2TSnXJKlthB1717p4",
    authDomain: "crmm-5c0ff.firebaseapp.com",
    projectId: "crmm-5c0ff",
    storageBucket: "crmm-5c0ff.firebasestorage.app",
    messagingSenderId: "43667131021",
    appId: "1:43667131021:web:ac9c84301468bc23d0f0c4",
    measurementId: "G-CLWJ2SHC58"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(clients.claim());
});

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.notification?.title || payload.data?.title || 'New Notification';
    const notifTag = payload.messageId || (notificationTitle + (payload.notification?.body || payload.data?.body || ''));
    const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: '/assets/img/logo.png',
        tag: notifTag,
        data: {
            url: payload.data?.url || '/'
        }
    };

    return self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
        if (clientList.length > 0) {
            // App is open (either foreground or background tab), don't show SW notification
            // The foreground script (firebase-init.blade.php) will handle it.
            return null;
        }
        return self.registration.showNotification(notificationTitle, notificationOptions);
    });
});

// Handle notification click
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const urlToOpen = event.notification.data.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url.includes(urlToOpen) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
