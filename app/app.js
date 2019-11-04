"use strict";

import Utils from './services/Utils.js'
import PushNotification from './services/PushNotification.js'
import HeaderBar from './views/components/HeaderBar.js'
import BottomBar from './views/components/BottomBar.js'
import Announcements from './views/pages/Announcements.js'
import AddResearch from './views/pages/AddResearch.js'
import Researches from './views/pages/Researches.js'
import TestNotification from './views/pages/TestNotification.js'
import AskPermission from "./views/pages/AskPermission.js";

document.addEventListener('DOMContentLoaded', async () => {
    if (!Utils.isBrowserCompatible()) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/app/serviceWorker.js');
        console.log('[SW] Service worker has been registered');
    } catch (e) {
        console.error('[SW] Service worker registration failed', e);
    }

    const headerBarContainer = document.querySelector('#headerbar-container');
    const bottomBarContainer = document.querySelector('#bottombar-container');

    headerBarContainer.innerHTML = await HeaderBar.render();
    bottomBarContainer.innerHTML = await BottomBar.render();
    window.onhashchange = router;

    if (!PushNotification.isNotificationActive()) {
        const mainContainer = document.querySelector('#main-container');
        mainContainer.innerHTML = AskPermission.render();
        AskPermission.after_render();
    } else {
        await PushNotification.push_updateSubscription();
        if (location.hash === '#/') {
            window.dispatchEvent(new HashChangeEvent("hashchange"));
        } else {
            location.hash = '#/';
        }
    }
});

const routes = {
    '/': Announcements
    , '/announcements': Announcements
    , '/researches': Researches
    , '/test-notification': TestNotification
    , '/add-research': AddResearch
};

const router = async () => {
    const content = document.querySelector('#main-container');
    let parsedURL = location.hash.slice(1);
    let page = routes[parsedURL] ? routes[parsedURL] : Announcements;
    content.innerHTML = await page.render();
    await page.after_render();
};
