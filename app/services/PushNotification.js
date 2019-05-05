const PushNotification = {

    applicationServerKey :
        'BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0'

    , notificationState: 'Disable Notification'

    , isNotificationPossible: true

    , changeState: (state) => {
        const pushButton = null || document.getElementById('push-subscription-button');
        switch (state) {
            case 'enabled':
                PushNotification.isNotificationPossible = true;
                break;
            case 'disabled':
                PushNotification.isNotificationPossible = true;
                PushNotification.notificationState = 'Disable Notification';
                break;
            case 'incompatible':
                PushNotification.isNotificationPossible = false;
                PushNotification.notificationState = 'Push notifications are not compatible with this browser';
                break;
            case 'denied':
                PushNotification.isNotificationPossible = false;
                PushNotification.notificationState = 'You have denied notifications';
                break;
            default:
                console.error('Unhandled push button state', state);
                break;
        }
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
            PushNotification.changeState('enabled');
        } catch (e) {
            if (Notification.permission === 'denied') {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                console.warn('Notifications are denied by the user.');
                PushNotification.changeState('denied');
            } else {
                // A problem occurred with the subscription; common reasons
                // include network errors or the user skipped the permission
                console.error('Impossible to subscribe to push notifications', e);
                PushNotification.changeState('disabled');
            }
        }
    }

    , push_updateSubscription: async () => {
        try {
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            PushNotification.changeState('disabled');
            if (!subscription) {
                // We aren't subscribed to push, so set UI to allow the user to enable push
                return;
            }
            // Keep your server in sync with the latest endpoint
            await PushNotification.push_sendSubscriptionToServer(subscription, 'PUT');
            // Set your UI to show they have subscribed for push messages
            PushNotification.changeState('enabled');
        } catch (e) {
            console.error('Error when updating the subscription', e);
        }
    }

    , push_unsubscribe: async () => {
        try {
            // To unsubscribe from push messaging, you need to get the subscription object
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            // Check that we have a subscription to unsubscribe
            if (!subscription) {
                // No subscription object, so set the state
                // to allow the user to subscribe to push
                PushNotification.changeState('disabled');
                return;
            }
            // We have a subscription, unsubscribe
            // Remove push subscription from server
            await PushNotification.push_sendSubscriptionToServer(subscription, 'DELETE');
            await subscription.unsubscribe();
            PushNotification.changeState('disabled');
        } catch (e) {
            // We failed to unsubscribe, NotificationsButton can lead to
            // an unusual state, so  it may be best to remove
            // the users data from your data store and
            // inform the user that you have done so
            console.error('Error when unsubscribing the user', e);
            PushNotification.changeState('disabled');
        }
    }

    , push_sendSubscriptionToServer: async (subscription, method) => {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        await fetch('/push-subscription', {
            method,
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                contentEncoding,
            }),
        });
        return subscription;
    }
};

export default PushNotification;