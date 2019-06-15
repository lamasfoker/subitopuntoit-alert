const ApiRequest = {
    //TODO: refactor the promise with await and parse the response

    post: async (url, body) => {
        let headers = new Headers();
        headers.set('Accept', 'application/json');

        return await fetch(url, {
            method: 'POST',
            headers,
            body: body
        }).then((response) => response.json());
    }

    , get: async (url) => {
        let headers = new Headers();
        headers.set('Accept', 'application/json');

        return await fetch(url, {
            method: 'GET',
            headers
        }).then((response) => response.json());
    }
};

export default ApiRequest;