// Firebase Cloud Messaging Background Service Worker for REOS CRM
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Initialize Firebase App in Service Worker
firebase.initializeApp({
  apiKey: "AIzaSyAPj-_g0FhEG9twwv0WjmOFB-5FK5nT3HI",
  authDomain: "reos-crm-69d5a.firebaseapp.com",
  databaseURL: "https://reos-crm-69d5a-default-rtdb.firebaseio.com",
  projectId: "reos-crm-69d5a",
  storageBucket: "reos-crm-69d5a.firebasestorage.app",
  messagingSenderId: "831733538173",
  appId: "1:831733538173:web:cf677b2b94d10a50368014",
  measurementId: "G-486TQK0JPR"
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
