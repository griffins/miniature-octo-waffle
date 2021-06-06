<?php


namespace App\Services\Polyline\HereMaps;


use Throwable;
use Exception;
use GuzzleHttp\Client;
use App\Services\Polyline;
use GuzzleHttp\RequestOptions;
use App\Services\Polyline\HereMaps\Utils\FlexiblePolyline;

class Api implements Polyline
{
    //Hack to avoid web scrappers getting this here docs api key
    public $opaque = [
        'UVPqHCPD0Or',
        'wZtTHe5le',
        '_NCoyk3u-SaH',
        'nbpLy0d5XHo'
    ];

    public function getClient()
    {
        $baseUrl = "https://router.hereapi.com";
        return new Client(['base_uri' => $baseUrl]);
    }
    /**
     * @throws Throwable
     */
    public function getPolyline($origin, $destination): array
    {
        $endPoint = "/v8/routes";
        $params = [
            'transportMode' => 'car',
            'origin' => implode(',', array_values($origin)),
            'destination' => implode(',', array_values($destination)),
            'apiKey' => implode('', $this->opaque),
            'return' => 'polyline'
        ];
        $res = $this->getClient()->get($endPoint, [RequestOptions::QUERY => $params]);
        $data = json_decode($res->getBody()->getContents());
        if (isset($data->notices)) {
            throw new Exception("Polyline Failed: ({$data->notices[0]->title})");
        } else {
            $polyLines = [];
            foreach ($data->routes as $route) {
                foreach ($route->sections as $section) {
                    $polyLines = array_merge($polyLines, FlexiblePolyline::decode($section->polyline)['polyline']);
                }
            }
            return $polyLines;
        }
    }
}
