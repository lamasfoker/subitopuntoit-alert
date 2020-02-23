"use strict";

import ApiRequest from "../../services/ApiRequest.js";

let TestNotification = {

    render: () => {
        return /*html*/ ``
    }

    , after_render: async (event) => {
        const headerTitle = document.getElementById('header-title');
        headerTitle.innerText = 'Notifica di Test';
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        if (!subscription) {
            M.toast({html: 'Per favore abilita le push notifications'});
            return;
        }
        let jsonResponse = await ApiRequest.post(
            '/test-notification',
            JSON.stringify({'endpoint': subscription.endpoint})
        );
        M.toast({html: jsonResponse.message});
    }
};

export default TestNotification;