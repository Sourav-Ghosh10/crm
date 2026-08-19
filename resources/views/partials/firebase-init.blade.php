<script type="module">
    // Import the functions you need from the SDKs you need
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js";

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
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Request Permission for notifications
    function requestPermission() {
        console.log('Requesting permission...');
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                console.log('Notification permission granted.');
                
                // Manually register the service worker with a cache buster
                navigator.serviceWorker.register('{{ asset('firebase-messaging-sw.js') }}?v=' + new Date().getTime())
                    .then((registration) => {
                        // Get token using our explicit registration
                        getToken(messaging, { 
                            vapidKey: 'BHtazpdXMyikWiRZg2cZE7DFhuDDx5KcjiKRuePoeAllcJXlVCYgRCfH2xT6hGzMy56b6A-opuwjnt5wKogMX0E',
                            serviceWorkerRegistration: registration 
                        })
                        .then((currentToken) => {
                            if (currentToken) {
                                console.log('FCM Token:', currentToken);
                            sendTokenToServer(currentToken);
                        } else {
                            console.log('No registration token available. Request permission to generate one.');
                        }
                    }).catch((err) => {
                        console.log('An error occurred while retrieving token. ', err);
                        alert("Firebase Push Setup Error: " + err.message);
                    });
                });
            } else {
                console.log('Unable to get permission to notify.');
            }
        });
    }

    // Attempt to request permission on load
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            requestPermission(); // To get token if already granted
        } else if (Notification.permission !== 'denied') {
             // Only request immediately if they haven't denied. In production, 
             // better to request on a user action like a button click, but this is fine for CRM.
            requestPermission();
        }
    }

    function sendTokenToServer(token) {
        fetch('{{ url('/fcm-token') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.__CSRF_TOKEN__
            },
            body: JSON.stringify({ token: token })
        }).then(response => response.json())
          .then(data => console.log('Token saved on server:', data))
          .catch(error => console.error('Error saving token:', error));
    }

    // Handle incoming messages while app is in foreground
    onMessage(messaging, (payload) => {
        console.log('Message received. ', payload);
        // Display a toast or browser notification natively
        if (Notification.permission === 'granted') {
             new Notification(payload.notification.title, {
                 body: payload.notification.body,
                 icon: '/assets/img/logo.png'
             });
        }
    });
</script>
