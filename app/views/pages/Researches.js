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

        let headers = new Headers();
        // Tell the server we want JSON back
        headers.set('Accept', 'application/json');

        //TODO: refactor the promise with await and parse the response

        let response = await fetch('/get-researches', {
            method: 'POST',
            headers,
            body: JSON.stringify(jsonData)
        }).then((response) => response.json());

        //TODO: check the response
        const listElement = document.getElementById("researches-list").firstElementChild;

        for (let i=0; i < response.list.length; i++ ) {
            let research = response.list[i];
            let cln = listElement.cloneNode(true);

            cln.children[1].innerHTML = research.query;
            cln.children[2].innerHTML = research.city+'<br>'+research.region;
            cln.children[3].addEventListener('click', async () => {
                let headers = new Headers();
                // Tell the server we want JSON back
                headers.set('Accept', 'application/json');

                //TODO: refactor the promise with await and parse the response

                let response = await fetch('/delete-research', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(Object.assign(research, {endpoint}))
                }).then((response) => response.json());

                //TODO: handle the response

                cln.parentNode.removeChild(cln);
            });

            document.getElementById("researches-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }
};

export default Researches;