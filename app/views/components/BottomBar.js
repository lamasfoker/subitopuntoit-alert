"use strict";

let BottomBar = {

    render: () => {
        return `
            <footer>
                <nav class="top-nav fixed-bottom-bar">
                    <div class="container">
                        <div class="nav-wrapper">
                            <div class="row menu">
                                <div class="col s12 center-align">
                                    <a class="btn-flat" href="#/researches"><i class="material-icons">search</i></a>
                                    <a class="btn-flat" href="#/announcements"><i class="material-icons">message</i></a>
                                    <a class="btn-flat" href="#/add-research"><i class="material-icons">add_box</i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </footer>
        `
    }
};

export default BottomBar;