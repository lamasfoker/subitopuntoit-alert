import PushNotification from "../../services/PushNotification.js";
import Init             from "../../services/Init.js";

let Settings = {

    render: async () => {
        return /*html*/ `
            <main>
                <ul class="collection">
                    <li class="collection-item"><i class="material-icons">person</i>Alvin</li>
                    <li class="collection-item"><i class="material-icons">my_location</i>Reggio Emilia</li>
                    <li class="collection-item" id="notification-on"><i class="material-icons">notifications_active</i>Enable Notification</li>
                    <li class="collection-item" id="notification-off" style="display: none"><i class="material-icons">notifications_off</i><span id="notification-message"></span></li>
                    <li class="collection-item" id="install" style="display: none"><i class="material-icons">flash_on</i>Install</li>
                    <li class="collection-item" id="impossible-install"><i class="material-icons">flash_off</i>I can't be installed or I am already installed</li>
                    <li class="collection-item" id="send-push-button"><i class="material-icons">notifications</i>Notification Test</li>
                </ul>
            </main>
        `
    }

    , after_render: async (event) => {
        const notificationOn = null || document.getElementById('notification-on');
        const notificationOff = null || document.getElementById('notification-off');
        const notificationMessage = null || document.getElementById('notification-message');

        notificationOn.addEventListener('click', async function () {
            await PushNotification.push_subscribe();
            notificationMessage.innerHTML = PushNotification.notificationState;
            notificationOn.style.display = 'none';
            notificationOff.style.display = 'block';
        });

        notificationOff.addEventListener('click', async function () {
            if (PushNotification.isNotificationPossible) {
                await PushNotification.push_unsubscribe();
                notificationMessage.innerHTML = PushNotification.notificationState;
                notificationOn.style.display = 'block';
                notificationOff.style.display = 'none';
            }
        });

        if (!Init.isBrowserCompatible()) {
            PushNotification.changeState('incompatible');
            notificationMessage.innerHTML = PushNotification.notificationState;
            notificationOn.style.display = 'none';
            notificationOff.style.display = 'block';
            return;
        }

        try {
            await navigator.serviceWorker.register('/app/serviceWorker.js');
            PushNotification.push_updateSubscription();
        } catch (e) {
            PushNotification.changeState('incompatible');
            notificationMessage.innerHTML = PushNotification.notificationState;
            notificationOn.style.display = 'none';
            notificationOff.style.display = 'block';
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