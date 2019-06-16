"use strict";

import Utils                        from './services/Utils.js'
import HeaderBar                    from './views/components/HeaderBar.js'
import BottomBar                    from './views/components/BottomBar.js'
import Announcements                from './views/pages/Announcements.js'
import AddResearch                  from './views/pages/AddResearch.js'
import Researches                   from './views/pages/Researches.js'
import Settings                     from './views/pages/Settings.js'
import Error404                     from './views/pages/Error404.js'

var installEvent = null;

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

    let Content = Settings;
    if (Settings.isNotificationActive()) {
        Content = Announcements;
    }

    headerBarContainer.innerHTML = await HeaderBar.render();
    mainContainer.innerHTML = await Content.render();
    await Content.after_render();
    bottomBarContainer.innerHTML = await BottomBar.render();

    location.hash = '/';
    window.onhashchange = router;
    window.onbeforeinstallprompt = postponeInstallation;
});

const routes = {
    '/'                     : Announcements
    , '/announcements'      : Announcements
    , '/researches'         : Researches
    , '/settings'           : Settings
    , '/add-research'       : AddResearch
};

const router = async () => {
    const content = null || document.querySelector('#main-container');
    let parsedURL = location.hash.slice(1);
    let page = routes[parsedURL] ? routes[parsedURL] : Error404;

    if (!Settings.isNotificationActive()) {
        page = Settings;
    }
    content.innerHTML = await page.render();
    // InstallEvent is useful only for Settings
    await page.after_render(installEvent);
};

const postponeInstallation = (event) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    event.preventDefault();
    // Stash the event so it can be triggered later.
    installEvent = event;
};