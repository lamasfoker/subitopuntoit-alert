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

    , ucFirst: (string) => {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    , ucAll: (phrase) => {
        let splitStr = phrase.toLowerCase().split(' ');
        for (let i = 0; i < splitStr.length; i++) {
            splitStr[i] = Init.ucFirst(splitStr[i]);
        }
        return splitStr.join(' ');
    }
};

export default Init;