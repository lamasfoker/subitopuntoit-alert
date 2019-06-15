import ApiRequest from "../../services/ApiRequest.js";

let AddResearch = {

    render : async () => {
        return `
            <div class="row">
                <form class="col s12" method="POST" action="" id="add-research-form">
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="city" id="city" type="text">
                            <label for="first_name">Provincia</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input name="region" id="region" type="text">
                            <label for="first_name">Regione</label>
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
                            <button type="submit">Invia</button>
                        </div>
                    </div>
                </form>
            </div>
        `
    }

    , after_render: async () => {
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const addResearchForm = null || document.getElementById('add-research-form');
        const headerTitle = null || document.getElementById('header-title');

        headerTitle.innerText = 'Aggiungi una Ricerca';

        addResearchForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const region = null || document.getElementById('region');
            const city = null || document.getElementById('city');
            const query = null || document.getElementById('query');

            const endpoint = subscription.toJSON().endpoint;
            let jsonForm = {'region': region.value, 'city': city.value, 'query': query.value};

            let jsonResponse = await ApiRequest.post(
                '/add-research',
                JSON.stringify(Object.assign(jsonForm, {endpoint}))
            );

            M.toast({html: jsonResponse.message});
        });
    }
};

export default AddResearch;