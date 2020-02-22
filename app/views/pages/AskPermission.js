"use strict";

import PushNotification from "../../services/PushNotification.js";

let AskPermission = {

    render: () => {
        return `
            <div class="modal">
                <div class="modal-content">
                    <h4>Permessi</h4>
                    <p>Dopo questo messaggio ti sarà chiesto di dare a Subito Alert la possibilità di mostrare notifiche. Se non accetterai l'applicazione non potrà funzionare correttamente.</p>
                </div>
                <div class="modal-footer">
                    <a href="#/add-research" class="modal-close waves-effect waves-green btn-flat">Ho capito</a>
                </div>
            </div>
        `
    }

    , after_render: () => {
        const modal = document.querySelector('.modal');
        const modalCloseButton = document.querySelector('.modal-close');
        M.Modal.init(modal, []);
        M.Modal.getInstance(modal).open();
        modalCloseButton.onclick = async () => {
            await PushNotification.push_subscribe();
            PushNotification.setNotificationActive(true);
        }
    }
};

export default AskPermission;