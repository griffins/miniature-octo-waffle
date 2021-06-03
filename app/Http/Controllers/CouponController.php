<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        return Coupon::query()->paginate();
    }

    public function listActive()
    {
        return Coupon::query()
            ->where('status', 'active')
            ->paginate();
    }

    public function store()
    {
        $this->validate(request(), [
            'code' => 'required|alphanum|unique:coupons',
            'description' => 'required',
            'amount' => 'numeric',
            'lat' => 'required_with:lng,radius',
            'lng' => 'required_with:lat,radius',
            'radius' => 'required_with:lat,lng',
            'status' => 'in:active,in-active',
            'validTo' => 'date',
            'validFrom' => 'date'
        ]);
        return Coupon::query()->create(request()->only(['code', 'description', 'amount', 'lat', 'lng', 'radius', 'status','validTo','validFrom']));
    }

    public function update()
    {
        $this->validate(request(), [
            'code' => [
                'required',
                'alphanum',
                Rule::unique('coupons')->ignore(request('id'))
            ],
            'description' => 'required',
            'amount' => 'numeric',
            'lat' => 'required_with:lng,radius',
            'lng' => 'required_with:lat,radius',
            'radius' => 'required_with:lat,lng',
            'status' => 'in:active,in-active'
        ]);
        return Coupon::query()->findOrFail(request('id'))->update(request()->only(['code', 'description', 'amount', 'lat', 'lng', 'radius', 'status']));
    }

    public function deactivate()
    {
        $coupon = Coupon::query()->findOrFail(request('id'));
        $coupon->status = 'in-active';
        $coupon->save();
        return $coupon;
    }

    /**
     * @throws \Exception
     */
    public function apply()
    {
        $this->validate(request(), [
            'destination' => 'required',
            'pickup' => 'required',
            'code' => 'required'
        ]);

        $coupon = Coupon::query()->where(['code' => request('code')])->firstOrFail();
        if ($coupon->validFrom && Carbon::now()->lessThan($coupon->validFrom)) {
            abort(422, "Coupon cannot be used before {$coupon->validFrom}.");
        } else {
            if ($coupon->validTo && Carbon::now()->greaterThan($coupon->validTo)) {
                abort(422, "Coupon cannot be used after {$coupon->validTo}.");
            } else {
                if ($coupon->status === 'in-active') {
                    abort(422, "Coupon was de-activated");
                } else {
                    if ($coupon->radius && !$coupon->validForTrip(request('destination'), request( 'pickup'))) {
                        abort(422, "Coupon can only be used to or from venue");
                    } else {
                        return ['coupon' => $coupon, 'polyline' => []];
                    }
                }
            }
        }
    }
    public function destroy()
    {
        return Coupon::query()->findOrFail(request('id'))->delete();
    }
}
