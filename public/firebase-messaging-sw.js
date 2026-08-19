importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyBGl6shSYtBtuz3PDjR1jbaECd4Q3zvNjQ",
    authDomain: "codecit-crm.firebaseapp.com",
    projectId: "codecit-crm",
    storageBucket: "codecit-crm.firebasestorage.app",
    messagingSenderId: "1019130586822",
    appId: "1:1019130586822:web:e7dfb009b2665cead619c7",
    measurementId: "G-KEGQV8DY8M"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/assets/img/logo.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
