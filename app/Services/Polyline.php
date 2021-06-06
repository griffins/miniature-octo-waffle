<?php


namespace App\Services;


interface Polyline
{
    public function getPolyline($origin, $destination): array;
}
