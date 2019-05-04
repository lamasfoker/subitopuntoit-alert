let BottomBar = {

    render: async () => {
        return `
            <footer>
                <nav class="top-nav fixed-bottom-bar">
                    <div class="container">
                        <div class="nav-wrapper">
                            <div class="row">
                                <div class="col s12 m10 offset-m1">
                                    <a class="btn-flat"><i class="material-icons">message</i></a>
                                    <a class="btn-flat"><i class="material-icons">search</i></a>
                                    <a class="btn-flat"><i class="material-icons">person</i></a>
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