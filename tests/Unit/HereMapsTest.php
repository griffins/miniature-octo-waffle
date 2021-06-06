<?php

namespace Unit;

use App\Services\Polyline;
use App\Services\Polyline\HereMaps\Api;
use App\Services\Polyline\HereMaps\Utils\FlexiblePolyline;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Mockery;
use TestCase;

class HereMapsTest extends TestCase
{
    use DatabaseMigrations;


    /**
     * Test whether we can get a the correct class to resolve a polyline.
     *
     * @return void
     */

    public function test_it_can_get_the_correct_class_to_resolve_a_polyline()
    {
        $class = $this->app->make(Polyline::class);
        $this->assertEquals(Api::class, get_class($class));
    }

    /**
     * Test whether we can decode a polyline.
     *
     * @return void
     */

    public function test_it_can_decode_a_polyline()
    {
        $decoded = FlexiblePolyline::decode('BlBoz5xJ67i1BU1B7PUzIhaUxL7YU');

        $expected = [
            'precision' => 5,
            'thirdDim' => 2,
            'thirdDimPrecision' => 0,
            'polyline' => [
                [50.10228, 8.69821, 10],
                [50.10201, 8.69567, 20],
                [50.10063, 8.6915, 30],
                [50.09878, 8.68752, 40]
            ]
        ];

        $this->assertEqualsCanonicalizing($expected, $decoded);
    }

    public function test_it_can_get_a_polyline_from_here_maps()
    {
        $json = json_encode(['routes' => [['sections' => [['polyline' => 'BlBoz5xJ67i1BU1B7PUzIhaUxL7YU']]]]]);
        $response = new Response(200, [], $json);
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')->andReturn($response);
        $mock = Mockery::mock(Api::class)->makePartial();
        $mock->shouldReceive('getClient')->andReturn($client);
        $polyline = $mock->getPolyline(['lat' => -1.232535, 'lng' => 36.878240], ['lat' => -1.438611, 'lng' => 36.777378]);
        $this->assertEquals(
            [
                [50.10228, 8.69821, 10],
                [50.10201, 8.69567, 20],
                [50.10063, 8.6915, 30],
                [50.09878, 8.68752, 40]
            ],
            $polyline);
        $this->assertEquals(Client::class, get_class((new Api())->getClient()));
    }
}
