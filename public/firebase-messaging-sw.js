// Firebase Cloud Messaging Background Service Worker for REOS CRM
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Initialize Firebase App in Service Worker
firebase.initializeApp({
  apiKey: "AIzaSyDevaG-S-L6_FbHUoJbwz3ZHIbRBmG1zNA",
  authDomain: "reos-77a5c.firebaseapp.com",
  databaseURL: "https://reos-77a5c-default-rtdb.firebaseio.com",
  projectId: "reos-77a5c",
  storageBucket: "reos-77a5c.firebasestorage.app",
  messagingSenderId: "623967369316",
  appId: "1:623967369316:web:57e8ba8c82ef4a08a74771"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background push message ', payload);

  const notificationTitle = payload.notification ? payload.notification.title : (payload.data ? payload.data.title : 'REOS Notification');
  const notificationOptions = {
    body: payload.notification ? payload.notification.body : (payload.data ? payload.data.body : ''),
    icon: '/images/logo.jpg',
    badge: '/images/logo.jpg',
    data: payload.data || {}
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const clickAction = event.notification.data ? event.notification.data.click_action : '/chat';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
      for (let i = 0; i < clientList.length; i++) {
        let client = clientList[i];
        if (client.url.includes(clickAction) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(clickAction || '/chat');
      }
    })
  );
});
