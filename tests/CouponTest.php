<?php

use Illuminate\Support\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CouponTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $mock = Mockery::spy(\App\Services\Polyline::class);
        $mock->shouldReceive('getPolyline')->andReturn(
            [
                [-1, 1], [23, 25]
            ]
        );
        $this->app->instance(
            \App\Services\Polyline::class, $mock
        );
    }

    /**
     * Basic sanity test of system.
     *
     * @return void
     */
    public function testSanity()
    {
        $this->get('/');

        $this->assertResponseOk();
    }

    /**
     * Test whether we can create a coupon.
     *
     * @return void
     */
    public function test_it_can_create_a_new_coupon()
    {
        $coupon = [
            'code' => 'KEN936',
            'description' => $this->faker->paragraph,
            'amount' => "500"
        ];
        $res = $this->json('post', '/coupon', $coupon);
        $this->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can delete a coupon.
     *
     * @return void
     */
    public function test_it_can_delete_a_coupon()
    {
        $coupon = [
            'code' => 'KEN936',
            'description' => $this->faker->paragraph,
            'amount' => "500"
        ];
        $res = $this->json('post', '/coupon', $coupon);
        $id = json_decode($res->response->getContent())->id;
        $this->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
        $this->json('delete', "/coupon/{$id}");
        $this->assertResponseOk();
        $res->notSeeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can update a coupon.
     *
     * @return void
     */
    public function test_it_can_update_a_new_coupon()
    {
        $coupon = [
            'code' => 'KEN936',
            'description' => $this->faker->paragraph,
            'amount' => "500"
        ];
        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
        $id = json_decode($res->response->getContent())->id;
        $res = $this->json('patch', "/coupon/{$id}", $coupon);
        $this->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can create coupon with invalid location restriction information.
     *
     * @return void
     */
    public function test_it_cannot_create_a_new_coupon_with_invalid_location_restrictions()
    {
        $coupon = [
            'code' => 'KEN936',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'lat' => $this->faker->latitude,
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $this->assertResponseStatus(422);
        $res->notSeeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can create coupon with invalid location restriction information.
     *
     * @return void
     */

    public function test_it_can_create_a_new_coupon_with_valid_location_restrictions()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'radius' => 23,
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $this->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can de-activate an active coupon .
     *
     * @return void
     */

    public function test_it_can_deactivate_a_valid_coupon()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'radius' => 23,
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
        $id = json_decode($res->response->getContent())->id;
        $res = $this->json('post', "/coupon/de-activate/{$id}", $coupon);
        $res->assertResponseOk();
        $coupon['status'] = 'in-active';
        $res->seeInDatabase('coupons', $coupon);
    }

    /**
     * Test whether we can list all coupons.
     *
     * @return void
     */

    public function test_it_can_list_all_created_coupons()
    {
        $coupons = [
            [
                'code' => 'KEN936',
                'description' => $this->faker->paragraph,
                'amount' => "500",
            ],
            [
                'code' => 'UGN935',
                'description' => $this->faker->paragraph,
                'amount' => "1500"
            ]
        ];

        foreach ($coupons as $coupon) {
            $this->json('post', '/coupon', $coupon);
            $this->assertResponseOk();
        }

        $res = $this->json('get', '/coupon', $coupon);
        $res->seeJsonContains($coupons[0]);
        $res->seeJsonContains($coupons[1]);
    }

    /**
     * Test whether we can list only active coupons.
     *
     * @return void
     */

    public function test_it_can_list_only_active_coupons()
    {
        $coupons = [
            [
                'code' => 'KEN936',
                'description' => $this->faker->paragraph,
                'amount' => "500"
            ],
            [
                'code' => 'UGN935',
                'description' => $this->faker->paragraph,
                'amount' => "1500",
            ]
        ];

        foreach ($coupons as $coupon) {
            $res = $this->json('post', '/coupon', $coupon);
            $this->assertResponseOk();
        }
        $id = json_decode($res->response->getContent())->id;
        $this->json('post', "/coupon/de-activate/{$id}", $coupon);
        $res = $this->json('get', '/coupon/active', $coupon);
        $res->seeJsonContains($coupons[0]);
        $res->seeJsonDoesntContains($coupons[1]);
    }

    /**
     * Test whether we cannot apply a de-activated coupon.
     *
     * @return void
     */

    public function test_it_cannot_apply_an_inactive_coupon()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'status' => 'in-active'
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $this->assertResponseOk();
        $res->seeInDatabase('coupons', $coupon);
        $details = ['code' => $coupon['code'], 'destination' => ['lat' => 12, 'lng' => 34], 'origin' => ['lat' => 12, 'lng' => 45]];
        $res = $this->json('post', '/coupon/apply', $details);
        $res->assertResponseStatus(422);
    }

    /**
     * Test whether we cannot apply an expired coupon.
     *
     * @return void
     */

    public function test_it_cannot_apply_an_expired_coupon()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'validTo' => Carbon::yesterday()
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $details = ['code' => $coupon['code'], 'destination' => ['lat' => 12, 'lng' => 34], 'origin' => ['lat' => 12, 'lng' => 45]];
        $res = $this->json('post', '/coupon/apply', $details);
        $res->assertResponseStatus(422);
    }

    /**
     * Test whether we cannot apply a future coupon.
     *
     * @return void
     */

    public function test_it_cannot_apply_an_future_coupon()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'validFrom' => Carbon::tomorrow()
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $details = ['code' => $coupon['code'], 'destination' => ['lat' => 12, 'lng' => 34], 'origin' => ['lat' => 12, 'lng' => 45]];
        $res = $this->json('post', '/coupon/apply', $details);
        $res->assertResponseStatus(422);
    }

    /**
     * Test whether we cannot apply a coupon outside its venue radius.
     *
     * @return void
     */

    public function test_it_cannot_apply_a_coupon_outside_venue_radius()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'lat' => 3.24524,
            'lng' => 45.67895,
            'radius' => 400
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $details = ['code' => $coupon['code'], 'destination' => ['lat' => 12, 'lng' => 34], 'origin' => ['lat' => 12.56, 'lng' => 45.90]];
        $res = $this->json('post', '/coupon/apply', $details);
        $res->assertResponseStatus(422);
    }

    /**
     * Test whether we cannot apply a coupon inside its venue radius.
     *
     * @return void
     */

    public function test_it_can_apply_a_coupon_inside_venue_radius()
    {
        $coupon = [
            'code' => 'KEN93',
            'description' => $this->faker->paragraph,
            'amount' => "500",
            'lat' => "-1.232568",
            'lng' => "36.878753",
            'radius' => "400"
        ];

        $res = $this->json('post', '/coupon', $coupon);
        $res->assertResponseOk();
        $details = ['code' => $coupon['code'], 'destination' => ['lat' => -1.232535, 'lng' => 36.878240], 'origin' => ['lat' => -1.438611, 'lng' => 36.777378]];
        $res = $this->json('post', '/coupon/apply', $details);
        $res->assertResponseStatus(200);
        $this->seeJsonContains($coupon);
    }
}
