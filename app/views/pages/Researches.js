"use strict";

import ApiRequest from "../../services/ApiRequest.js";
import Utils from "../../services/Utils.js";

let Researches = {

    render: () => {
        return `
            <ul id="researches-list" class="collection">
                <li style="display: none" class="collection-item avatar">
                    <a class="delete"><i class="material-icons circle blue">delete</i></a>
                    <span class="title"></span>
                    <p class="location"></p>
                    <i class="material-icons secondary-content is-in-title">title</i>
                </li>
            </ul>
        `
    }

    , empty_render: async () => {
        return `
            <div class="no-elements">
                <img src="/app/assets/images/no-elements.svg" alt="no researches saved">
                <h5>Ops...</h5>
                <div>
                    <p>non hai ancora salvato nessuno ricerca.</p>
                </div>
            </div>
        `
    }

    , after_render: async () => {
        const headerTitle = document.getElementById('header-title');
        headerTitle.innerText = 'Ricerche Salvate';

        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonData = {'endpoint': endpoint};

        let jsonResponse = await ApiRequest.post(
            '/get-researches',
            JSON.stringify(jsonData)
        );

        if (jsonResponse.code === 404) {
            const content = document.querySelector('#main-container');
            content.innerHTML = await Researches.empty_render();
            return;
        }
        if (jsonResponse.code !== 200) {
            M.toast({html: jsonResponse.message});
            return;
        }

        const listElement = document.getElementById("researches-list").firstElementChild;

        for (let i = 0; i < jsonResponse.data.length; i++) {
            let research = jsonResponse.data[i];
            let locationInfo = Researches.getLocationInfo(research.location_parameters.split(' '));
            let cln = listElement.cloneNode(true);

            cln.querySelector('.title').innerHTML = Utils.ucFirst(research.query);
            cln.querySelector('.location').innerHTML = locationInfo + research.location;
            if (research.is_only_in_title === false) {
                cln.querySelector('.is-in-title').style.display = 'none';
            }
            cln.querySelector('.delete').addEventListener('click', () => {
                Researches.deleteResearch(Object.assign(research.id, {endpoint}), cln);
            });

            document.getElementById("researches-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }

    , getLocationInfo: (parameters) => {
        if (parameters.length === 2) {
            return 'Provincia di '
        }
        if (parameters.length === 3) {
            return 'Comune di '
        }
        if (parameters[0] === 'italia') {
            return 'Tutta '
        }
        return 'Regione ';
    }

    , deleteResearch: async (jsonBody, element) => {
        let jsonResponse = await ApiRequest.post(
            '/delete-research',
            JSON.stringify(jsonBody)
        );
        if (jsonResponse.code === 200) {
            element.parentNode.removeChild(element);
        }
        M.toast({html: jsonResponse.message});
    }
};

export default Researches;