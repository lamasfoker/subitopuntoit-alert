const ApiRequest = {
    Post: async (url, body) => {
        let headers = new Headers();
        // Tell the server we want JSON back
        headers.set('Accept', 'application/json');

        //TODO: refactor the promise with await and parse the response

        return await fetch(url, {
            method: 'POST',
            headers,
            body: body
        }).then((response) => response.json());
    }
};

export default ApiRequest;