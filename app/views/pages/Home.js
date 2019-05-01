let Home = {

    render : async () => {
        return `
            <main>
                <div class="container">
                    <div class="row">
                        <div class="col s12 m8 offset-m1 xl7 offset-xl1">
                            <button id="button" style="display: none">Add To HomeScreen</button>
                            <div id="notification-button-container"></div>
                            <button id="send-push-button">Send a push notification</button>
                        </div>
                    </div>
                </div>
            </main>
        `
    }
};

export default Home;