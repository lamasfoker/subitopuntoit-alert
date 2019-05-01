const Init = {
    isBrowserCompatible: () => {
        if (!('serviceWorker' in navigator)) {
            console.warn('Service workers are not supported by this browser');
            return false;
        }

        if (!('PushManager' in window)) {
            console.warn('Push notifications are not supported by this browser');
            return false;
        }

        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
            console.warn('Notifications are not supported by this browser');
            return false;
        }
        // Check the current Notification permission.
        // If its denied, the button should appears as such, until the user changes the permission manually
        if (Notification.permission === 'denied') {
            console.warn('Notifications are denied by the user');
            return false;
        }
        return true
    }
};

export default Init;