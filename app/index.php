<?php

require __DIR__.'/../vendor/autoload.php';

/**
 * @param string $storedAnnouncementTime
 * @param string $region
 * @param string $city
 * @param string $query
 * @return JsonSerializable
 */
function getAnnouncementUpdate($storedAnnouncementTime, $region, $city, $query)
{
    $url = getUrl($region, $city, $query);
    $data = getJsonData($url);

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

    $announcementString = '{"status": "new announcements", "list": '.extractUpdate($data->list, $storedAnnouncementTime).'}';
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
function getUrl($region, $city, $query)
{
    return 'https://www.subito.it/annunci-' . $region . '/vendita/usato/' . $city . '/?q=' . $query;
}

/**
 * @param array $data
 * @param string $storedAnnouncementTime
 * @return string
 */
function extractUpdate($data, $storedAnnouncementTime)
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
function getJsonData($url)
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

echo json_encode(getAnnouncementUpdate('2019-04-06 17:40:42', 'emilia-romagna', 'modena', 'ps4'));