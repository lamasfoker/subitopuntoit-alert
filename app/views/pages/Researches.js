import ApiRequest from "../../services/ApiRequest.js";

let Researches = {

    render : async () => {
        return `
            <ul id="researches-list" class="collection">
                <li style="display: none" class="collection-item avatar">
                    <a class="delete"><i class="material-icons circle blue">delete</i></a>
                    <span class="title"></span>
                    <p class="location"></p>
                </li>
            </ul>
        `
    }

    , after_render: async () => {
        const headerTitle = null || document.getElementById('header-title');
        headerTitle.innerText = 'Ricerche Salvate';

        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonData = {'endpoint': endpoint};

        let jsonResponse = await ApiRequest.Post(
            '/get-researches',
            JSON.stringify(jsonData)
        );

        if (jsonResponse.code !== 200) {
            M.toast({html: jsonResponse.message});
            return;
        }

        const listElement = document.getElementById("researches-list").firstElementChild;

        for (let i=0; i < jsonResponse.data.length; i++ ) {
            let research = jsonResponse.data[i];
            let cln = listElement.cloneNode(true);

            cln.getElementsByClassName('title')[0].innerHTML = research.query;
            cln.getElementsByClassName('location')[0].innerHTML = research.city+' - '+research.region;
            cln.getElementsByClassName('delete')[0].addEventListener('click', async () => {

                let jsonResponse = await ApiRequest.Post(
                    '/delete-research',
                    JSON.stringify(Object.assign(research, {endpoint}))
                );

                if (jsonResponse.code === 200) {
                    cln.parentNode.removeChild(cln);
                }
                M.toast({html: jsonResponse.message});
            });

            document.getElementById("researches-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }
};

export default Researches;