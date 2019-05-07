let AddResearch = {

    render : async () => {
        return `
            <div class="row">
                <form class="col s12" method="POST" action="" id="add-research-form">
                    <div class="row">
                        <div class="input-field col s12">
                            <input placeholder="Reggio Emilia" name="city" id="city" type="text" class="validate">
                            <label for="first_name">Provincia</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input placeholder="Emilia Romagna" name="region" id="region" type="text" class="validate">
                            <label for="first_name">Regione</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input placeholder="Macchina" name="query" id="query" type="text" class="validate">
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

        addResearchForm.addEventListener('submit', async function (event) {

            let headers = new Headers();
            // Tell the server we want JSON back
            headers.set('Accept', 'application/json');

            const region = null || document.getElementById('region');
            const city = null || document.getElementById('city');
            const query = null || document.getElementById('query');
            const endpoint = subscription.toJSON().endpoint;

            let jsonForm = {'region': region.value, 'city': city.value, 'query': query.value};

            //TODO: refactor the promise with await and parse the response
            //TODO: handle the response

            fetch('/add-research', {
                method: 'POST',
                headers,
                body: JSON.stringify(Object.assign(jsonForm, {endpoint})),
            }).then(function (response) {
                return response.json();
            }).then(function (jsonData) {
                alert(JSON.parse(jsonData).status);
            });

            event.preventDefault();
        });
    }
};

export default AddResearch;