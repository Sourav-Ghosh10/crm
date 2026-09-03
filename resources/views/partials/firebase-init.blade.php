<script type="module">
    // Import the functions you need from the SDKs you need
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js";

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
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Setup notification sound element
    function setupFirebaseUI() {
        if (document.body && !document.getElementById('crm-notification-sound')) {
            const audio = document.createElement('audio');
            audio.id = 'crm-notification-sound';
            audio.src = '{{ asset('/notification.wav') }}';
            audio.preload = 'auto';
            document.body.appendChild(audio);
        }
    }

    // Call UI setup when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFirebaseUI);
    } else {
        setupFirebaseUI();
    }

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
                            vapidKey: 'BBtw91KqAowZiEXM9mvZhJeSdWSq--FERPUAWv1CCTdZhVYt57jqqnu4dKuGicMti2BwJLMoPjmJ4SoO5x8sRAA',
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
            // Only request immediately if they haven't denied.
            requestPermission();
        }
    }

    function getBrowserInfo() {
        const ua = navigator.userAgent;
        let browserName = "Unknown";
        if (ua.includes("Firefox/")) browserName = "Firefox";
        else if (ua.includes("Edg/")) browserName = "Edge";
        else if (ua.includes("Chrome/")) browserName = "Chrome";
        else if (ua.includes("Safari/") && !ua.includes("Chrome/")) browserName = "Safari";

        let deviceType = "Desktop";
        if (/Mobile|Android|iP(hone|od|ad)/.test(ua)) {
            deviceType = "Mobile";
        }
        return { browser: browserName, device: deviceType };
    }

    function sendTokenToServer(token) {
        const info = getBrowserInfo();
        fetch('{{ url('/fcm-token') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.__CSRF_TOKEN__
            },
            body: JSON.stringify({
                token: token,
                browser: info.browser,
                device: info.device
            })
        }).then(response => response.json())
            .then(data => console.log('Token saved on server:', data))
            .catch(error => console.error('Error saving token:', error));
    }

    function playNotificationSound() {
        const audio = document.getElementById('crm-notification-sound');
        if (audio) {
            audio.play().catch(e => console.log('Audio play blocked by browser:', e));
        }
    }

    // Handle incoming messages while app is in foreground
    onMessage(messaging, (payload) => {
        console.log('Message received in foreground. ', payload);

        const title = payload.notification?.title || payload.data?.title || 'New Notification';
        const body = payload.notification?.body || payload.data?.body || '';
        const url = payload.data?.url || null;

        // 1. Show browser notification natively (only if app is not actively focused)
        if ('Notification' in window && Notification.permission === 'granted' && !document.hasFocus()) {
            // Use a unique tag based on the message ID or content
            const notifTag = payload.messageId || (title + body);

            // Deduplicate across tabs using localStorage (prevents 3 tabs from showing 3 popups)
            const recentKey = 'fcm_notif_' + notifTag;
            if (localStorage.getItem(recentKey)) {
                console.log('Notification already shown by another tab. Skipping.');
                return;
            }
            localStorage.setItem(recentKey, '1');
            setTimeout(() => localStorage.removeItem(recentKey), 3000); // clear after 3s

            navigator.serviceWorker.ready.then((registration) => {
                registration.showNotification(title, {
                    body: body,
                    icon: '/assets/img/logo.png',
                    tag: notifTag,
                    data: { url: url }
                });
            });
        }
    });

</script>