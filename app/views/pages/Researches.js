import ApiRequest from "../../services/ApiRequest.js";

let Researches = {

    render : async () => {
        return `
            <ul id="researches-list" class="collection">
                <li style="display: none" class="collection-item avatar">
                    <i class="material-icons circle blue">search</i>
                    <span class="title">Title</span>
                    <p>Content</p>
                    <a class="secondary-content"><i class="material-icons">delete</i></a>
                </li>
            </ul>
        `
    }

    , after_render: async () => {
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonData = {'endpoint': endpoint};

        let jsonResponse = await ApiRequest.Post(
            '/get-researches',
            JSON.stringify(jsonData)
        );
        //TODO: check the jsonResponse

        const listElement = document.getElementById("researches-list").firstElementChild;

        for (let i=0; i < jsonResponse.list.length; i++ ) {
            let research = jsonResponse.list[i];
            let cln = listElement.cloneNode(true);

            cln.children[1].innerHTML = research.query;
            cln.children[2].innerHTML = research.city+'<br>'+research.region;
            cln.children[3].addEventListener('click', async () => {

                let jsonResponse = await ApiRequest.Post(
                    '/delete-research',
                    JSON.stringify(Object.assign(research, {endpoint}))
                );
                //TODO: handle the jsonResponse

                cln.parentNode.removeChild(cln);
            });

            document.getElementById("researches-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }
};

export default Researches;