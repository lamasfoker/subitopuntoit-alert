import ApiRequest from "../../services/ApiRequest.js";

let Announcements = {

    render : async () => {
        return `
            <div class="row">
                <div id="announcements-list" class="section">
                    <div class="col s12 m7" style="display: none;">
                        <div class="small card">
                            <a href="#/error" target="_blank">
                                <div class="card-image">
                                    <img src="/app/assets/images/no-photo-available.png" alt="announcement-image">
                                </div>
                            </a>
                            <div class="card-content">
                                <p class="title"></p>
                                <p class="price"></p>
                                <p class="town"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `
    }

    , after_render: async () => {
        const headerTitle = null || document.getElementById('header-title');
        headerTitle.innerText = 'Annunci';

        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonData = {'endpoint': endpoint};

        let jsonResponse = await ApiRequest.post(
            '/get-announcements',
            JSON.stringify(jsonData)
        );

        if (jsonResponse.code !== 200) {
            M.toast({html: jsonResponse.message});
            return;
        }

        const listElement = document.getElementById("announcements-list").firstElementChild;

        for (let i=0; i < jsonResponse.data.length; i++ ) {
            let announcement = JSON.parse(jsonResponse.data[i]);
            let cln = listElement.cloneNode(true);
            announcement.date = announcement.date.slice(0, -3);

            if (announcement.price === 'undefined') {
                announcement.price = 'Prezzo non definito';
            }

            if (announcement.imageUrl !== 'undefined') {
                cln.querySelector('img').setAttribute('src', announcement.imageUrl);
            }
            cln.querySelector('a').setAttribute('href', announcement.url);
            cln.querySelector('.title').innerHTML = announcement.name;
            cln.querySelector('.price').innerHTML = announcement.price;
            cln.querySelector('.town').innerHTML = announcement.date+' - '+announcement.town;

            document.getElementById("announcements-list").appendChild(cln);
            cln.style.display = 'block';
        }
    }
};

export default Announcements;