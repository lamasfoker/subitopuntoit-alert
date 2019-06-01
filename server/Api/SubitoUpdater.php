<?php

namespace SubitoPuntoItAlert\Api;

use Requests;

class SubitoUpdater
{
    /**
     * @param string $storedAnnouncementTime
     * @param string $region
     * @param string $city
     * @param string $query
     * @return Response
     */
    public function getAnnouncementUpdate($storedAnnouncementTime, $region, $city, $query): Response
    {
        $url = $this->getUrl($region, $city, $query);
        $data = $this->getJsonData($url);
        $response = new Response();

        if (!$data) {
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
        if (strcmp($lastAnnouncementTime, $storedAnnouncementTime) <= 0) {
            $response->setHttpCode(204);
            $response->setMessage('no update');
            return $response;
        }

        $response->setHttpCode(200);
        $response->setMessage('new announcements');
        $data = $this->extractUpdate($data['list'], $storedAnnouncementTime);
        $response->setData($data);

        return $response;
    }


    /**
     * @param string $region
     * @param string $city
     * @param string $query
     * @return string
     */
    private function getUrl($region, $city, $query): string
    {
        $region = preg_replace('/[^a-z]/', '-', strtolower($region));
        $city = preg_replace('/[^a-z]/', '-', strtolower($city));
        $query = urlencode($query);
        return 'https://www.subito.it/annunci-' . $region . '/vendita/usato/' . $city . '/?q=' . $query;
    }

    /**
     * @param array $data
     * @param string $storedAnnouncementTime
     * @return array
     */
    private function extractUpdate($data, $storedAnnouncementTime): array
    {
        $extractedUpdate = [];
        foreach ($data as $key => $announcement) {
            $announcement = $announcement['item'];
            $announcementTime = $announcement['date'];
            if (strcmp($announcementTime, $storedAnnouncementTime) <= 0) {
                break;
            }
            $extractedUpdate[$key] = [];
            $extractedUpdate[$key]['price'] = isset($announcement['features']['/price'])?$announcement['features']['/price']['values'][0]['value']:'undefined';
            $extractedUpdate[$key]['town'] = $announcement['geo']['town']['value'];
            $extractedUpdate[$key]['imageUrl'] = isset($announcement['images'][0])?$announcement['images'][0]['scale'][4]['secureuri']:'undefined';
            $extractedUpdate[$key]['date'] = $announcement['date'];
            $extractedUpdate[$key]['name'] = addcslashes($announcement['subject'], '"\\/');
            $extractedUpdate[$key]['url'] = $announcement['urls']['default'];
        }
        return $extractedUpdate;
    }

    /**
     * @param  string url
     * @return array|null
     */
    private function getJsonData($url):? array
    {
        $firstStringDelimiter = '__NEXT_DATA__ = ';
        $secondStringDelimeter = ';__NEXT_LOADED_PAGES__';
        $response = Requests::get($url);
        if ($response->status_code !== 200) {
            return null;
        }
        $dataStart = strpos($response->body, $firstStringDelimiter) + strlen($firstStringDelimiter);
        $dataLength = strpos($response->body, $secondStringDelimeter) - $dataStart;
        $data = substr($response->body, $dataStart, $dataLength);
        return json_decode($data, true)['props']['state']['items'];
    }

}
