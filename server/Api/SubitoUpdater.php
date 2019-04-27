<?php

namespace SubitoPuntoItAlert\Api;

use JsonSerializable;
use Requests;

class SubitoUpdater
{
    /**
     * @param string $storedAnnouncementTime
     * @param string $region
     * @param string $city
     * @param string $query
     * @return JsonSerializable
     */
    public function getAnnouncementUpdate($storedAnnouncementTime, $region, $city, $query)
    {
        $url = $this->getUrl($region, $city, $query);
        $data = $this->getJsonData($url);

        if (!$data) {
            return json_decode('{"status": "url error"}');
        }

        $announcementNumber = $data->total;
        if ($announcementNumber <= 0 ) {
            return json_decode('{"status": "no announcement"}');
        }

        $lastAnnouncementTime = $data->list[0]->item->date;
        if (strcmp($lastAnnouncementTime, $storedAnnouncementTime) <= 0) {
            return json_decode('{"status": "no update"}');
        }

        $announcementString = '{"status": "new announcements", "list": '.$this->extractUpdate($data->list, $storedAnnouncementTime).'}';
        $announcementJson = json_decode($announcementString);
        if (!$announcementJson) {
            return json_decode('{"status": "something went wrong"}');
        }

        return $announcementJson;
    }


    /**
     * @param string $region
     * @param string $city
     * @param string $query
     * @return string
     */
    private function getUrl($region, $city, $query)
    {
        return 'https://www.subito.it/annunci-' . $region . '/vendita/usato/' . $city . '/?q=' . $query;
    }

    /**
     * @param array $data
     * @param string $storedAnnouncementTime
     * @return string
     */
    private function extractUpdate($data, $storedAnnouncementTime)
    {
        $extractedUpdate = '{';
        foreach ($data as $key => $announcement) {
            $announcement = $announcement->item;
            $announcementTime = $announcement->date;
            if (strcmp($announcementTime, $storedAnnouncementTime) <= 0) {
                break;
            }
            $name = '/price';
            $price = isset($announcement->features->$name)?$announcement->features->$name->values[0]->value:'undefined';
            $town = $announcement->geo->town->value;
            $imageUrl = isset($announcement->images[0])?$announcement->images[0]->scale[4]->secureuri:'undefined';
            $date = $announcement->date;
            $name = addcslashes($announcement->subject, '"\\/');
            $url = $announcement->urls->default;
            $extractedUpdate .= '"'.$key.'": { "name": "'.$name.'", "price": "'.$price.'", "town": "'.$town.'", "imageUrl": "'.$imageUrl.'", "date": "'.$date.'", "url": "'.$url.'"}, ';
        }
        $extractedUpdate = substr($extractedUpdate, 0, -2);
        $extractedUpdate .= '}';
        return $extractedUpdate;
    }

    /**
     * @param  string url
     * @return JsonSerializable|null
     */
    private function getJsonData($url)
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
        return json_decode($data)->props->state->items;
    }

}
