import NotificationsButton from "../components/NotificationsButton.js";
import PushNotification from "../../services/PushNotification.js";
import Init from "../../services/Init.js";

let Settings = {

    render: async () => {
        return /*html*/ `
            <main>
                <ul class="collection">
                    <li class="collection-item"><i class="material-icons">person</i>Alvin</li>
                    <li class="collection-item"><i class="material-icons">my_location</i>Reggio Emilia</li>
                    <li class="collection-item">
                        <button id="push-subscription-button">
                            <i class="material-icons">notifications_active</i>Active Notifications
                            <i class="material-icons">notifications_off</i>Disable Notifications
                        </button>
                        <div id="notification-button-container"></div>
                    </li>
                    <li class="collection-item">
                        <button id="button" style="display: none"><i class="material-icons">flash_on</i>Install</button>
                        <i class="material-icons">flash_off</i>I can't be installed
                    </li>
                    <li class="collection-item"><button id="send-push-button"><i class="material-icons">notifications</i>Test Notification</button></li>
                </ul>
            </main>
        `
    }

    , after_render: async () => {
        const notificationButtonContainer = null || document.getElementById('notification-button-container');
        notificationButtonContainer.innerHTML = await NotificationsButton.render();

        const pushButton = null || document.getElementById('push-subscription-button');

        pushButton.addEventListener('click', function () {
            if (NotificationsButton.isPushEnabled) {
                PushNotification.push_unsubscribe();
            } else {
                PushNotification.push_subscribe();
            }
        });


        if (!Init.isBrowserCompatible()) {
            NotificationsButton.changePushButtonState('incompatible');
            return;
        }

        try {
            await navigator.serviceWorker.register('/app/serviceWorker.js');
            console.log('[SW] Service worker has been registered');
            PushNotification.push_updateSubscription();
        } catch (e) {
            console.error('[SW] Service worker registration failed', e);
            NotificationsButton.changePushButtonState('incompatible');
        }

        /**
         * START send_push_notification
         * this part handles the button that calls the endpoint that triggers a notification
         * in the real world, you wouldn't need this, because notifications are typically sent from backend logic
         */

        const sendPushButton = null || document.querySelector('#send-push-button');

        sendPushButton.addEventListener('click', async () => {
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            if (!subscription) {
                alert('Please enable push notifications');
                return;
            }
            const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
            const jsonSubscription = subscription.toJSON();
            fetch('/push-notification', {
                method: 'POST',
                body: JSON.stringify(Object.assign(jsonSubscription, {contentEncoding})),
            });
        });

        /**
         * END send_push_notification
         */

        const addButton = null || document.getElementById('button');
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (event) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            event.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = event;
            // Update UI notify the user they can add to home screen
            addButton.style.display = 'block';
        });
        addButton.addEventListener('click', async (event) => {
            // hide our user interface that shows our A2HS button
            addButton.style.display = 'none';
            // Show the prompt
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            let choiceResult = await deferredPrompt.userChoice;
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
        });
    }
};

export default Settings;