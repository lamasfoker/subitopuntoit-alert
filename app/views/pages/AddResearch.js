"use strict";

import ApiRequest from "../../services/ApiRequest.js";

let AddResearch = {

    render: () => {
        return `
            <div class="row">
                <form class="col s12" method="POST" action="" id="add-research-form">
                    <div class="row label">
                        <div class="col s12">
                            <h6 class="label-inner">Luogo</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 text">
                            <input name="location" id="location" type="text" class="browser-default autocomplete" autocomplete="off" value="Tutta Italia">
                        </div>
                    </div>
                    <div class="row label">
                        <div class="col s12">
                            <h6 class="label-inner">Termine di Ricerca</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 text">
                            <input name="query" id="query" type="text" autocomplete="off" class="browser-default">
                        </div>
                    </div>
                    <div class="row">
                        <div class=" col s12">
                            <label>
                                <input type="checkbox" class="filled-in" id="only-title"/>
                                <span class="label-inner">Cerca solo nel Titolo</span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <button class="blue-button" type="submit">Salva</button>
                        </div>
                    </div>
                </form>
            </div>
        `
    }

    , after_render: async () => {
        const form = document.querySelector('#add-research-form');
        const headerTitle = document.querySelector('#header-title');
        const autoCompleteLocation = document.querySelector('.autocomplete');
        const location = document.querySelector('#location');

        location.onfocus = () => {
            location.value = "";
            location.onfocus = null;
        };
        M.Autocomplete.init(autoCompleteLocation, {limit: 7});
        headerTitle.innerText = 'Aggiungi una Ricerca';
        autoCompleteLocation.oninput = AddResearch.updateAutoCompleteLocation;
        form.onsubmit = AddResearch.sendResearch;
    }

    , updateAutoCompleteLocation: async (event) => {
        let inputText = event.target;
        let autocomplete = M.Autocomplete.getInstance(inputText);
        let jsonResponse = await ApiRequest.get('/get-location?q=' + inputText.value);
        if (jsonResponse.code !== 200) {
            return;
        }

        let locations = {};
        for (let i = 0; i < jsonResponse.data.length; i++) {
            let dataLocation = jsonResponse.data[i];
            let label = dataLocation.region.value + ' regione';
            if ('city' in dataLocation) {
                label = dataLocation.city.value + ' e provincia';
            }
            if ('town' in dataLocation) {
                label = dataLocation.town.value + ' (' + dataLocation.city.short_name + ') comune';
            }
            locations[label] = null;
        }
        autocomplete.updateData(locations);
    }

    , sendResearch: async (event) => {
        event.preventDefault();
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const location = document.querySelector('#location');
        const query = document.querySelector('#query');
        const checkbox = document.querySelector('#only-title');
        const endpoint = subscription.toJSON().endpoint;

        let jsonForm = {
            'location': location.value,
            'only_title': checkbox.checked,
            'query': query.value,
            'endpoint': endpoint
        };

        let jsonResponse = await ApiRequest.post(
            '/add-research',
            JSON.stringify(jsonForm)
        );

        M.toast({html: jsonResponse.message});
    }
};

export default AddResearch;