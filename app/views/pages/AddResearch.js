import ApiRequest from "../../services/ApiRequest.js";
import Utils      from "../../services/Utils.js";

let AddResearch = {

    render : async () => {
        return `
            <div class="row">
                <form class="col s12" method="POST" action="" id="add-research-form">
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="location" id="location" type="text" class="autocomplete">
                            <label for="first_name">Dove</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="query" id="query" type="text">
                            <label for="first_name">Ricerca</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <label>
                                <input type="checkbox" class="filled-in" id="only-title"/>
                                <span>Cerca solo nel Titolo</span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <button type="submit">Invia</button>
                        </div>
                    </div>
                </form>
            </div>
        `
    }

    , after_render: async () => {
        const form = null || document.querySelector('#add-research-form');
        const headerTitle = null || document.querySelector('#header-title');
        const autoCompleteLocation = null || document.querySelector('.autocomplete');

        M.Autocomplete.init(autoCompleteLocation);
        headerTitle.innerText = 'Aggiungi una Ricerca';
        autoCompleteLocation.oninput = AddResearch.updateAutoCompleteLocation;
        form.onsubmit = AddResearch.sendResearch;
    }

    , locations : {}

    , updateAutoCompleteLocation : async (event) => {
        let inputText = event.target;
        let autocomplete = M.Autocomplete.getInstance(inputText);
        let jsonResponse = await ApiRequest.get('/get-location?q=' + inputText.value);
        if (jsonResponse.code !== 200) {
            return;
        }

        let dataLocation = jsonResponse.data;
        let locations = {};
        for (let i=0; i<dataLocation.length; i++) {
            if ('town' in dataLocation[i]) {
                locations[dataLocation[i].town.value] = null;
                AddResearch.locations[dataLocation[i].town.value] =
                    dataLocation[i].region.friendly_name + ' ' +
                    dataLocation[i].city.friendly_name + ' ' +
                    dataLocation[i].town.friendly_name;
                continue;
            }
            if ('city' in dataLocation[i]) {
                locations[dataLocation[i].city.value] = null;
                AddResearch.locations[dataLocation[i].city.value] =
                    dataLocation[i].region.friendly_name + ' ' +
                    dataLocation[i].city.friendly_name;
                continue;
            }
            locations[dataLocation[i].region.value] = null;
            AddResearch.locations[dataLocation[i].region.value] =
                dataLocation[i].region.friendly_name;
        }
        autocomplete.updateData(locations);
    }

    , sendResearch : async (event) => {
        event.preventDefault();
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const location = null || document.querySelector('#location');
        const query = null || document.querySelector('#query');
        const checkbox = null || document.querySelector('#only-title');
        const endpoint = subscription.toJSON().endpoint;
        let locationParameter = 'null';
        if (AddResearch.locations[Utils.ucAll(location.value)]) {
            locationParameter = AddResearch.locations[Utils.ucAll(location.value)];
        }

        let jsonForm = {
            'location': Utils.ucAll(location.value),
            'location_parameters' : locationParameter,
            'only_title' : checkbox.checked,
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