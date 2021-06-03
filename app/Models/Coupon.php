<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'description', 'amount', 'lat', 'lng', 'radius', 'status', 'validTo', 'validFrom'];
    protected $dates = [
        'validFrom',
        'validTo'
    ];

    public function smallestDistance($one, $two)
    {
        // Haversine Formula
        $radius = 6371000; // in meters since we want to use meters as our units
        $lat = ($one['lat'] - $two['lat']) * pi() / 180;
        $lng = ($one['lng'] - $two['lng']) * pi() / 180;
        $a = sin($lat / 2) * sin($lat / 2) +
            cos($one['lat'] * pi() / 180) * cos($two['lat'] * pi() / 180) *
            sin($lng / 2) * sin($lng / 2);
        $c = 2 * asin(sqrt($a));
        return $radius * $c;
    }

    public function validForTrip($destination, $pickup)
    {
        $venue = $this->only('lng', 'lat');
        $closestLocationToVenue = min($this->smallestDistance($venue, $destination), $this->smallestDistance($venue, $pickup));
        return $closestLocationToVenue < floatval($this->radius);
    }
}
