// Firebase Cloud Messaging Service Worker
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Parse query params if injected, or use default Firebase configuration
const urlParams = new URLSearchParams(location.search);
const firebaseConfig = {
    apiKey: "AIzaSyD6jpgKOUzP6mDXtFVOZNh7KMHIemc8Kc8",
    authDomain: "barakah-c44d4.firebaseapp.com",
    projectId: "barakah-c44d4",
    storageBucket: "barakah-c44d4.firebasestorage.app",
    messagingSenderId: "86263442374",
    appId: "1:86263442374:web:bb0228b229b477cb4ca0c7",
    measurementId: "G-CNFXMCXTY7"
};

try {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
        console.log('[firebase-messaging-sw.js] Received background message: ', payload);
        const notificationTitle = payload.notification?.title || payload.data?.title || 'Barakah Wallet';
        const notificationOptions = {
            body: payload.notification?.body || payload.data?.body || 'New transaction received.',
            icon: payload.notification?.icon || '/images/icons/icon-192x192.png',
            badge: '/images/icons/icon-72x72.png',
            vibrate: [100, 50, 100],
            data: {
                click_action: payload.data?.click_action || payload.fcmOptions?.link || '/transactions'
            }
        };

        self.registration.showNotification(notificationTitle, notificationOptions);
    });
} catch (e) {
    console.warn('[firebase-messaging-sw.js] Firebase initialization warning:', e);
}

// Background notification click listener
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const clickUrl = event.notification.data?.click_action || '/transactions';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes(clickUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(clickUrl);
            }
        })
    );
});

