const ApiRequest = {
    post: async (url, body) => {
        let headers = new Headers();
        headers.set('Accept', 'application/json');

        let response = await fetch(url, {
            method: 'POST',
            headers,
            body: body
        });

        return response.json();
    }

    , get: async (url) => {
        let headers = new Headers();
        headers.set('Accept', 'application/json');

        let response = await fetch(url, {
            method: 'GET',
            headers
        });

        return response.json();
    }
};

export default ApiRequest;