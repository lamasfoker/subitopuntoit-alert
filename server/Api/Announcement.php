<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Api;

use SubitoPuntoItAlert\Database\Model\Research;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class Announcement
{
    /**
     * @param Research $research
     * @return Response
     */
    public function getAnnouncements(Research $research): Response
    {
        $url = $this->getUrl($research);
        $data = $this->getJsonData($url);
        $response = new Response();
        $lastCheck = $research->getLastCheck();

        if (empty($data)) {
            $response->setHttpCode(400);
            $response->setMessage('url error');
            return $response;
        }

        $announcementNumber = $data['total'];
        if ($announcementNumber <= 0 ) {
            $response->setHttpCode(204);
            $response->setMessage('no announcement');
            return $response;
        }

        $lastAnnouncementTime = $data['list'][0]['item']['date'];
        if (strcmp($lastAnnouncementTime, $lastCheck) <= 0) {
            $response->setHttpCode(204);
            $response->setMessage('no update');
            return $response;
        }

        $response->setHttpCode(200);
        $response->setMessage('new announcements');
        $data = $this->extractUpdate($data['list'], $lastCheck);
        $response->setData($data);

        return $response;
    }

    /**
     * @param Research $research
     * @return bool
     */
    public function validate(Research $research): bool
    {
        $url = $this->getUrl($research);
        return !empty($this->getJsonData($url));
    }


    /**
     * @param Research $research
     * @return string
     */
    private function getUrl(Research $research): string
    {
        $locationParameters = explode(' ', $research->getLocationParameters());
        $region = $locationParameters[0];
        $city = '';
        $town = '';
        if (count($locationParameters) >= 2) {
            $city = '/' . $locationParameters[1];
        }
        if (count($locationParameters) === 3) {
            $town = '/'. $locationParameters[2];
        }
        $isOnlyInTitle = $research->isOnlyInTitle() ? 'true' : 'false';
        $query = urlencode($research->getQuery());
        return 'https://www.subito.it/annunci-' . $region . '/vendita/usato' . $city . $town . '/?qso=' . $isOnlyInTitle . '&q=' . $query;
    }

    /**
     * @param array $data
     * @param string $lastCheck
     * @return array
     */
    private function extractUpdate(array $data, string $lastCheck): array
    {
        $extractedUpdate = [];
        foreach ($data as $key => $announcement) {
            $announcement = $announcement['item'];
            $announcementTime = $announcement['date'];
            if (strcmp($announcementTime, $lastCheck) <= 0) {
                break;
            }

            $extractedUpdate[$key] = [];
            if (array_key_exists('/price', $announcement['features'])) {
                $extractedUpdate[$key]['price'] = $announcement['features']['/price']['values'][0]['value'];
            } else {
                $extractedUpdate[$key]['price'] = null;
            }
            $extractedUpdate[$key]['town'] = $announcement['geo']['town']['value'];
            if (count($announcement['images']) > 0) {
                $extractedUpdate[$key]['imageUrl'] = $announcement['images'][0]['scale'][4]['secureuri'];
            } else {
                $extractedUpdate[$key]['imageUrl'] = null;
            }
            $extractedUpdate[$key]['date'] = $announcement['date'];
            $extractedUpdate[$key]['name'] = addcslashes($announcement['subject'], '"\\/');
            $extractedUpdate[$key]['url'] = $announcement['urls']['default'];
        }
        return $extractedUpdate;
    }

    /**
     * @param string $url
     * @return array
     */
    private function getJsonData(string $url): array
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', $url);
            $crawler = new Crawler($response->getContent());
            $data = $crawler->filter('script#__NEXT_DATA__')->text();
            return json_decode($data, true)['props']['state']['items'];
        } catch (ExceptionInterface $e) {
            return [];
        }
    }
}
