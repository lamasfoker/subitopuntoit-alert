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

        return true
    }
};

export default Init;