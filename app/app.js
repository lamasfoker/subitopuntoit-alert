"use strict";

import Init                         from './services/Init.js'
import HeaderBar                    from './views/components/HeaderBar.js'
import BottomBar                    from './views/components/BottomBar.js'
import Announcements                from './views/pages/Announcements.js'
import AddResearch                  from './views/pages/AddResearch.js'
import Researches                   from './views/pages/Researches.js'
import Settings                     from './views/pages/Settings.js'
import Error404                     from './views/pages/Error404.js'

var installEvent = null;

// Listen on page load:
document.addEventListener('DOMContentLoaded', async () => {
    if (!Init.isBrowserCompatible()) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/app/serviceWorker.js');
        console.log('[SW] Service worker has been registered');
    } catch (e) {
        console.error('[SW] Service worker registration failed', e);
    }

    const headerBarContainer = null || document.getElementById('headerbar-container');
    const mainContainer = null || document.getElementById('main-container');
    const bottomBarContainer = null || document.getElementById('bottombar-container');

    headerBarContainer.innerHTML = await HeaderBar.render();
    mainContainer.innerHTML = await Announcements.render();
    await Announcements.after_render();
    bottomBarContainer.innerHTML = await BottomBar.render();

    window.addEventListener('beforeinstallprompt', (event) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        event.preventDefault();
        // Stash the event so it can be triggered later.
        installEvent = event;
    });
});

// List of supported routes. Any url other than these routes will throw a 404 error
const routes = {
    '/'                     : Announcements
    , '/announcements'      : Announcements
    , '/researches'         : Researches
    , '/settings'           : Settings
    , '/add-research'       : AddResearch
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
