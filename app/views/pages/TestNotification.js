import ApiRequest       from "../../services/ApiRequest.js";

let TestNotification = {

    render: async () => {
        return /*html*/ ``
    }

    , after_render: async (event) => {
        const headerTitle = null || document.getElementById('header-title');
        headerTitle.innerText = 'Notifica di Test';
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        if (!subscription) {
            alert('Please enable push notifications');
            return;
        }
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
        const jsonSubscription = subscription.toJSON();
        await ApiRequest.post(
            '/test-notification',
            JSON.stringify(Object.assign(jsonSubscription, {contentEncoding}))
        );
    }
};

export default TestNotification;