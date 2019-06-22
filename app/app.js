"use strict";

import Utils                        from './services/Utils.js'
import PushNotification             from './services/PushNotification.js'
import HeaderBar                    from './views/components/HeaderBar.js'
import BottomBar                    from './views/components/BottomBar.js'
import Announcements                from './views/pages/Announcements.js'
import AddResearch                  from './views/pages/AddResearch.js'
import Researches                   from './views/pages/Researches.js'
import TestNotification             from './views/pages/TestNotification.js'
import Error404                     from './views/pages/Error404.js'

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

    const headerBarContainer = null || document.querySelector('#headerbar-container');
    const mainContainer = null || document.querySelector('#main-container');
    const bottomBarContainer = null || document.querySelector('#bottombar-container');

    let Content = Announcements;
    if (!PushNotification.isNotificationActive()) {
        await PushNotification.push_subscribe();
        PushNotification.setNotificationActive(true);
        Content = AddResearch;
    } else {
        await PushNotification.push_updateSubscription();
    }

    headerBarContainer.innerHTML = await HeaderBar.render();
    mainContainer.innerHTML = await Content.render();
    await Content.after_render();
    bottomBarContainer.innerHTML = await BottomBar.render();

    location.hash = '/';
    window.onhashchange = router;
});

const routes = {
    '/'                     : Announcements
    , '/announcements'      : Announcements
    , '/researches'         : Researches
    , '/test-notification'  : TestNotification
    , '/add-research'       : AddResearch
};

const router = async () => {
    const content = null || document.querySelector('#main-container');
    let parsedURL = location.hash.slice(1);
    let page = routes[parsedURL] ? routes[parsedURL] : Error404;
    content.innerHTML = await page.render();
    await page.after_render();
};
