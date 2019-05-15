let Announcements = {

    render : async () => {
        return `
            <ul id="announcements-list" class="collection content">
                <li style="display: none;" class="collection-item avatar">
                    <img src="https://tiny.cc/jyjq6y" class="circle" alt="">
                    <span class="title">Title</span>
                    <p>Content</p>
                    <a href="#/" target="_blank" class="secondary-content"><i class="material-icons">launch</i></a>
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

        let response = await fetch('/get-announcements', {
            method: 'POST',
            headers,
            body: JSON.stringify(jsonData)
        }).then((response) => response.json());

        //TODO: check the response

        const listElement = document.getElementById("announcements-list").firstElementChild;

        for (let i=0; i < response.list.length; i++ ) {
            let announcement = JSON.parse(response.list[i]);
            let cln = listElement.cloneNode(true);

            cln.children[0].setAttribute('src', announcement.imageUrl);
            cln.children[1].innerHTML = announcement.name;
            cln.children[2].innerHTML = announcement.price+'<br>'+announcement.town+'<br>'+announcement.date;
            cln.children[3].setAttribute('href', announcement.url);

            document.getElementById("announcements-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }
};

export default Announcements;