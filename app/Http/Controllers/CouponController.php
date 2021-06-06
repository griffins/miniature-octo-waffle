<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\Polyline;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $this->validate($request, [
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
        return Coupon::query()->create(request()->only(['code', 'description', 'amount', 'lat', 'lng', 'radius', 'status', 'validTo', 'validFrom']));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'code' => [
                'required',
                'alphanum',
                Rule::unique('coupons')->ignore($request->id)
            ],
            'description' => 'required',
            'amount' => 'numeric',
            'lat' => 'required_with:lng,radius',
            'lng' => 'required_with:lat,radius',
            'radius' => 'required_with:lat,lng',
            'status' => 'in:active,in-active'
        ]);
        return Coupon::query()->findOrFail(request('id'))->update($request->only(['code', 'description', 'amount', 'lat', 'lng', 'radius', 'status']));
    }

    public function deactivate(Request $request)
    {
        $coupon = Coupon::query()->findOrFail($request->id);
        $coupon->status = 'in-active';
        $coupon->save();
        return $coupon;
    }

    /**
     * @throws \Exception
     */
    public function apply(Polyline $maps, Request $request)
    {
        $this->validate(request(), [
            'destination' => 'required',
            'origin' => 'required',
            'code' => 'required'
        ]);

        list($destination, $origin) = array_values($request->only('destination', 'origin'));

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
                    if ($coupon->radius && !$coupon->validForTrip($destination, $origin)) {
                        abort(422, "Coupon can only be used to or from venue");
                    } else {
                        try {
                            return [
                                'coupon' => $coupon,
                                'polyline' => $maps->getPolyline($destination, $origin)
                            ];
                        } catch (\Throwable $t) {
                            abort(400, $t->getMessage());
                        }
                    }
                }
            }
        }
    }

    public function destroy(Request $request)
    {
        return Coupon::query()->findOrFail($request->id)->delete();
    }
}
