let NotificationsButton = {

    render: async () => {
        return `
            <button id="push-subscription-button">Push notifications !</button>
        `
    }

    , changePushButtonState: (state) => {
        const pushButton = null || document.getElementById('push-subscription-button');
        switch (state) {
            case 'enabled':
                pushButton.disabled = false;
                pushButton.textContent = 'Disable Push notifications';
                NotificationsButton.isPushEnabled = true;
                break;
            case 'disabled':
                pushButton.disabled = false;
                pushButton.textContent = 'Enable Push notifications';
                NotificationsButton.isPushEnabled = false;
                break;
            case 'computing':
                pushButton.disabled = true;
                pushButton.textContent = 'Loading...';
                break;
            case 'incompatible':
                pushButton.disabled = true;
                pushButton.textContent = 'Push notifications are not compatible with this browser';
                break;
            default:
                console.error('Unhandled push button state', state);
                break;
        }
    }

    , isPushEnabled: false
};

export default NotificationsButton;