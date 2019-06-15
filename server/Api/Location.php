<?php

namespace SubitoPuntoItAlert\Api;

use Requests;

class Location
{
    /**
     * @param string $query
     * @return Response
     */
    public function getLocation(string $query): Response
    {
        $url = $this->getUrl($query);
        $data = $this->getJsonData($url);

        $response = new Response();

        if (is_null($data)) {
            $response->setHttpCode(400);
            $response->setMessage('url error');
            return $response;
        }

        if ($data === []) {
            $response->setHttpCode(204);
            $response->setMessage('no location found');
            return $response;
        }

        $response->setHttpCode(200);
        $response->setMessage('locations found');
        $response->setData($data);

        return $response;
    }

    /**
     * @param string $query
     * @return string
     */
    private function getUrl(string $query): string
    {
        $query = urlencode($query);
        return 'https://www.subito.it/hades/v1/geo/search?key=' . $query . '&lim=20';
    }

    /**
     * @param string $url
     * @return array|null
     */
    private function getJsonData(string $url):? array
    {
        $response = Requests::get($url);
        if ($response->status_code !== 200) {
            return null;
        }
        return json_decode($response->body, true)['data'];
    }
}
