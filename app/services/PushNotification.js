import NotificationButton       from '../views/components/NotificationsButton.js'

const PushNotification = {

    applicationServerKey :
        'BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0'

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
        return new Promise((resolve, reject) => {
            if (Notification.permission === 'denied') {
                return reject(new Error('Push messages are blocked.'));
            }

            if (Notification.permission === 'granted') {
                return resolve();
            }

            if (Notification.permission === 'default') {
                return Notification.requestPermission().then(result => {
                    if (result !== 'granted') {
                        reject(new Error('Bad permission result'));
                    }

                    resolve();
                });
            }
        });
    }

    , push_subscribe: () => {
        NotificationButton.changePushButtonState('computing');

        return PushNotification.checkNotificationPermission()
            .then(() => navigator.serviceWorker.ready)
            .then(serviceWorkerRegistration =>
                serviceWorkerRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: PushNotification.urlBase64ToUint8Array(PushNotification.applicationServerKey),
                })
            )
            .then(subscription => {
                // Subscription was successful
                // create subscription on your server
                return PushNotification.push_sendSubscriptionToServer(subscription, 'POST');
            })
            .then(subscription => subscription && NotificationButton.changePushButtonState('enabled')) // update your UI
            .catch(e => {
                if (Notification.permission === 'denied') {
                    // The user denied the notification permission which
                    // means we failed to subscribe and the user will need
                    // to manually change the notification permission to
                    // subscribe to push messages
                    console.warn('Notifications are denied by the user.');
                    NotificationButton.changePushButtonState('incompatible');
                } else {
                    // A problem occurred with the subscription; common reasons
                    // include network errors or the user skipped the permission
                    console.error('Impossible to subscribe to push notifications', e);
                    NotificationButton.changePushButtonState('disabled');
                }
            });
    }

    , push_updateSubscription: () => {
        navigator.serviceWorker.ready
            .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
            .then(subscription => {
                NotificationButton.changePushButtonState('disabled');

                if (!subscription) {
                    // We aren't subscribed to push, so set UI to allow the user to enable push
                    return;
                }

                // Keep your server in sync with the latest endpoint
                return PushNotification.push_sendSubscriptionToServer(subscription, 'PUT');
            })
            .then(subscription => subscription && NotificationButton.changePushButtonState('enabled')) // Set your UI to show they have subscribed for push messages
            .catch(e => {
                console.error('Error when updating the subscription', e);
            });
    }

    , push_unsubscribe: () => {
        NotificationButton.changePushButtonState('computing');

        // To unsubscribe from push messaging, you need to get the subscription object
        navigator.serviceWorker.ready
            .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
            .then(subscription => {
                // Check that we have a subscription to unsubscribe
                if (!subscription) {
                    // No subscription object, so set the state
                    // to allow the user to subscribe to push
                    NotificationButton.changePushButtonState('disabled');
                    return;
                }

                // We have a subscription, unsubscribe
                // Remove push subscription from server
                return PushNotification.push_sendSubscriptionToServer(subscription, 'DELETE');
            })
            .then(subscription => subscription.unsubscribe())
            .then(() => NotificationButton.changePushButtonState('disabled'))
            .catch(e => {
                // We failed to unsubscribe, NotificationsButton can lead to
                // an unusual state, so  it may be best to remove
                // the users data from your data store and
                // inform the user that you have done so
                console.error('Error when unsubscribing the user', e);
                NotificationButton.changePushButtonState('disabled');
            });
    }

    , push_sendSubscriptionToServer: (subscription, method) => {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        return fetch('/push-subscription', {
            method,
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                contentEncoding,
            }),
        }).then(() => subscription);
    }

};

export default PushNotification;