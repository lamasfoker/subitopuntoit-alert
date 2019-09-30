let Error404 = {

    render : async () => {
        return `
            <section class="section">
                <h1> 404 Error </h1>
            </section>
        `
    }

    , after_render: async () => {
        const headerTitle = document.getElementById('header-title');
        headerTitle.innerText = 'Pagina non Trovata'
    }
};

export default Error404;