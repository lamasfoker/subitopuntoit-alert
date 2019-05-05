"use strict";

import Init                         from './services/Init.js'
import PushNotification             from './services/PushNotification.js'
import NotificationsButton          from './views/components/NotificationsButton.js'
import HeaderBar                    from './views/components/HeaderBar.js'
import BottomBar                    from './views/components/BottomBar.js'
import Home                         from './views/pages/Home.js'
import Announcements                from './views/pages/Announcements.js'
import Researches                   from './views/pages/Researches.js'
import Settings                     from './views/pages/Settings.js'
import Error404                     from './views/pages/Error404.js'

var installEvent = null;

// Listen on page load:
document.addEventListener('DOMContentLoaded', async () => {

    const headerBarContainer = null || document.getElementById('headerbar-container');
    const mainContainer = null || document.getElementById('main-container');
    const bottomBarContainer = null || document.getElementById('bottombar-container');
    headerBarContainer.innerHTML = await HeaderBar.render();
    mainContainer.innerHTML = await Home.render();
    bottomBarContainer.innerHTML = await BottomBar.render();

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

    window.addEventListener('beforeinstallprompt', (event) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        event.preventDefault();
        // Stash the event so it can be triggered later.
        installEvent = event;
    });
});

// List of supported routes. Any url other than these routes will throw a 404 error
const routes = {
    '/'                     : Home //TODO: makes home announcements
    , '/announcements'      : Announcements
    , '/researches'         : Researches
    , '/settings'           : Settings
};


// The router code. Takes a URL, checks against the list of supported routes and then renders the corresponding content page.
const router = async () => {

    // Lazy load view element:
    const content = null || document.getElementById('main-container');

    // Get the parsed URl from the addressbar
    let parsedURL = location.hash.slice(1);

    // Get the page from our hash of supported routes.
    // If the parsed URL is not in our list of supported routes, select the 404 page instead
    let page = routes[parsedURL] ? routes[parsedURL] : Error404;
    content.innerHTML = await page.render();
    // InstallEvent is useful only for Settings
    await page.after_render(installEvent);

};

// Listen on hash change:
window.addEventListener('hashchange', router);
