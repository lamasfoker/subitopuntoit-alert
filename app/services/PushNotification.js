const PushNotification = {

    applicationServerKey :
        'BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0'

    , isNotificationActive: () => {
        return localStorage.getItem('isNotificationActive') === 'true';
    }

    , setNotificationActive: (state) => {
        localStorage.setItem('isNotificationActive', state);
    }
    
    , urlBase64ToUint8Array: (base64String) => {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;

    }

    , checkNotificationPermission: () => {
        return new Promise(async (resolve, reject) => {
            if (Notification.permission === 'denied') {
                reject(new Error('Push messages are blocked.'));
            }

            if (Notification.permission === 'granted') {
                resolve();
            }

            if (Notification.permission === 'default') {
                const result = await Notification.requestPermission();
                if (result !== 'granted') {
                    reject(new Error('Bad permission result'));
                }
                resolve();
            }
        });
    }

    , push_subscribe: async () => {
        try {
            await PushNotification.checkNotificationPermission();
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: PushNotification.urlBase64ToUint8Array(PushNotification.applicationServerKey),
            });
            // Subscription was successful
            // create subscription on your server
            await PushNotification.push_sendSubscriptionToServer(subscription, 'POST');
            // update your UI
            M.toast({html: 'Inscrizione alle notifiche avvenuta con successo'});
        } catch (e) {
            if (Notification.permission === 'denied') {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                console.warn('Notifications are denied by the user.');
                M.toast({html: 'Inscrizione alle notifiche non permessa'});
            } else {
                // A problem occurred with the subscription; common reasons
                // include network errors or the user skipped the permission
                console.error('Impossible to subscribe to push notifications', e);
                M.toast({html: 'Inscrizione alle notifiche impossibile da effettuare'});
            }
        }
    }

    , push_updateSubscription: async () => {
        try {
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            if (!subscription) {
                // We aren't subscribed to push, so set UI to allow the user to enable push
                await PushNotification.push_subscribe();
                return;
            }
            // Keep your server in sync with the latest endpoint
            await PushNotification.push_sendSubscriptionToServer(subscription, 'PUT');
            // Set your UI to show they have subscribed for push messages
        } catch (e) {
            console.error('Error when updating the subscription', e);
            M.toast({html: 'Errore: non è possibile ricevere notifiche'});
        }
    }

    , push_sendSubscriptionToServer: async (subscription, method) => {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        let response = await fetch('/push-subscription', {
            method,
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                contentEncoding,
            }),
        });

        if (response.json().code !== 200) {
            throw 'Server respond with error'
        }

        return subscription;
    }
};

export default PushNotification;