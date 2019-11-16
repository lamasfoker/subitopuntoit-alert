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
            return $response->setHttpCode(204)
                ->setMessage('no location found');
        }

        return $response->setHttpCode(200)
            ->setMessage('locations found')
            ->setData($data);
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
