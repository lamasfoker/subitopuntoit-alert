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
                    <li class="collection-item" id="install" style="display: none"><i class="material-icons">flash_on</i>Install</li>
                    <li class="collection-item" id="impossible-install"><i class="material-icons">flash_off</i>I can't be installed or I am already installed</li>
                    <li class="collection-item"><button id="send-push-button"><i class="material-icons">notifications</i>Test Notification</button></li>
                </ul>
            </main>
        `
    }

    , after_render: async (event) => {
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
        const installMessage = null || document.getElementById('install');
        const impossibleInstallMessage = null || document.getElementById('impossible-install');
        let installEvent = event;

        window.addEventListener('beforeinstallprompt', (event) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            event.preventDefault();
            // Update UI notify the user they can add to home screen
            installMessage.style.display = 'block';
            // Stash the event so it can be triggered later.
            installEvent = event;
        });

        if (installEvent){
            installMessage.style.display = 'block';
            impossibleInstallMessage.style.display = 'none';
        }

        installMessage.addEventListener('click', async (event) => {
            // Show the prompt
            installEvent.prompt();
            // Wait for the user to respond to the prompt
            let choiceResult = await installEvent.userChoice;
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
                installMessage.style.display = 'none';
                impossibleInstallMessage.style.display = 'block';
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            installEvent = null;
        });
    }
};

export default Settings;