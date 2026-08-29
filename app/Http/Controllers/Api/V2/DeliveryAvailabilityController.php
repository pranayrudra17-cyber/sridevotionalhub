<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\DeliveryAvailabilityService;
use Illuminate\Http\Request;

class DeliveryAvailabilityController extends Controller
{
    public function check(Request $request)
    {
        $pincode = isset($request->pincode) ? $request->pincode : $request->postal_code;

        return response()->json((new DeliveryAvailabilityService)->checkResponse($pincode));
    }
}
