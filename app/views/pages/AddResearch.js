import ApiRequest from "../../services/ApiRequest.js";

let AddResearch = {

    render : async () => {
        return `
            <div class="row">
                <form class="col s12" method="POST" action="" id="add-research-form">
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="location" id="location" type="text" class="autocomplete" autocomplete="off">
                            <label for="first_name">Dove</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="query" id="query" type="text" autocomplete="off">
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

    , updateAutoCompleteLocation : async (event) => {
        let inputText = event.target;
        let autocomplete = M.Autocomplete.getInstance(inputText);
        let jsonResponse = await ApiRequest.get('/get-location?q=' + inputText.value);
        if (jsonResponse.code !== 200) {
            return;
        }

        let locations = {};
        for (let i=0; i<jsonResponse.data.length; i++) {
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

    , sendResearch : async (event) => {
        event.preventDefault();
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const location = null || document.querySelector('#location');
        const query = null || document.querySelector('#query');
        const checkbox = null || document.querySelector('#only-title');
        const endpoint = subscription.toJSON().endpoint;

        let jsonForm = {
            'location': location.value,
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