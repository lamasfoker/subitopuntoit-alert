import ApiRequest from "../../services/ApiRequest.js";

let Announcements = {

    render : async () => {
        return `
            <ul id="announcements-list" class="collection content">
                <li style="display: none;" class="collection-item avatar">
                    <img src="https://tiny.cc/jyjq6y" class="circle" alt="">
                    <span class="title">Title</span>
                    <p>Content</p>
                    <a href="#/error" target="_blank" class="secondary-content"><i class="material-icons">launch</i></a>
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
            '/get-announcements',
            JSON.stringify(jsonData)
        );
        //TODO: check the jsonResponse

        const listElement = document.getElementById("announcements-list").firstElementChild;

        for (let i=0; i < jsonResponse.list.length; i++ ) {
            let announcement = JSON.parse(jsonResponse.list[i]);
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