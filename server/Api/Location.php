<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Api;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

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
     * @return array
     */
    private function getJsonData(string $url): array
    {
        $client = HttpClient::create();
        try {
            $response = $response = $client->request('GET', $url);
            return $response->toArray()['data'];
        } catch (ExceptionInterface $e) {
            return [];
        }
    }
}
