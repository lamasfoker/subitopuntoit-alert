"use strict";

import ApiRequest from "../../services/ApiRequest.js";

let Announcements = {

    render: () => {
        return `
            <div class="row">
                <div id="announcements-list" class="section">
                    <div class="col s12 m7" style="display: none;">
                        <div class="small card">
                            <a href="#/error" target="_blank">
                                <div class="card-image">
                                    <img src="/app/assets/images/no-photo-available.svg" alt="announcement-image" loading="lazy">
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

    , empty_render: async () => {
        return `
            <div class="no-elements">
                <img src="/app/assets/images/no-elements.svg" alt="no researches saved">
                <h5>Ops...</h5>
                <div>
                    <p>non abbiamo trovato annunci per le tue ricerche.</p>
                </div>
            </div>
        `
    }

    , after_render: async () => {
        const headerTitle = document.getElementById('header-title');
        headerTitle.innerText = 'Annunci';

        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonData = {'endpoint': endpoint};

        let jsonResponse = await ApiRequest.post(
            '/get-announcements',
            JSON.stringify(jsonData)
        );

        if (jsonResponse.code === 404) {
            const content = document.querySelector('#main-container');
            content.innerHTML = await Announcements.empty_render();
            return;
        }
        if (jsonResponse.code !== 200) {
            M.toast({html: jsonResponse.message});
            return;
        }

        const listElement = document.getElementById("announcements-list").firstElementChild;

        for (let i = 0; i < jsonResponse.data.length; i++) {
            let announcement = JSON.parse(jsonResponse.data[i].details);
            let cln = listElement.cloneNode(true);
            let clnImage = cln.querySelector('img');
            announcement.date = announcement.date.slice(0, -3);

            if (announcement.imageUrl !== null) {
                clnImage.setAttribute('src', announcement.imageUrl);
            }
            cln.querySelector('a').setAttribute('href', announcement.url);
            cln.querySelector('.title').innerHTML = announcement.name;
            cln.querySelector('.price').innerHTML = announcement.price;
            if (announcement.town === null) {
                announcement.town = 'Italia';
            }
            cln.querySelector('.town').innerHTML = announcement.date + ' - ' + announcement.town;

            document.getElementById("announcements-list").appendChild(cln);
            cln.style.display = 'block';
            Announcements.addDeleteBehaviour(cln, jsonResponse.data[i].id);
        }
    }

    , addDeleteBehaviour: (card, announcementId) => {
        let movement = {};

        card.addEventListener('touchstart', (event) => {
            movement.x1 = event.changedTouches[0].screenX;
            movement.y1 = event.changedTouches[0].screenY;
        }, false);

        card.addEventListener('touchend', async (event) => {
            movement.x2 = event.changedTouches[0].screenX;
            movement.y2 = event.changedTouches[0].screenY;
            await Announcements.swipeAnimation(card, movement);
            card.remove();
            await Announcements.deleteAnnouncement(announcementId);
        }, false);
    }

    , swipeAnimation: (card, movement) => {
        if (
            Math.abs(movement.x2 - movement.x1) < 150 ||
            Math.abs(movement.y2 - movement.y1) > 50
        ) {
            throw 'doesn\'t swipe, too short or wrong movement';
        }

        let initialFrame = {
            transform: 'translateX(0)',
            transformOrigin: '50% 50%',
            filter: 'blur(0)',
            opacity: 1
        };

        let finalFrame = {
            transform: '',
            transformOrigin: '50% 0',
            filter: 'blur(40px)',
            opacity: 0
        };

        if (movement.x2 > movement.x1) {
            //move card to the right
            finalFrame.transform = 'translateX(1000px)';
        } else {
            //move card to the left
            finalFrame.transform = 'translateX(-1000px)';
        }

        let cardAnimation = card.animate([initialFrame, finalFrame], 600);
        //TODO: use 'await cardAnimation.finished;' when browser support it
        //      see https://developer.mozilla.org/en-US/docs/Web/API/Animation/finished
        return new Promise((resolve) => {
            cardAnimation.addEventListener('finish', resolve);
        });
    }

    , deleteAnnouncement: async (announcementId) => {
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
        const endpoint = subscription.toJSON().endpoint;

        let jsonBody = {
            'id': announcementId,
            'endpoint': endpoint
        };

        let jsonResponse = await ApiRequest.post(
            '/delete-announcement',
            JSON.stringify(jsonBody)
        );
        if (jsonResponse.code !== 200) {
            M.toast({html: jsonResponse.message});
        }
    }
};

export default Announcements;