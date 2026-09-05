<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    /**
     * Parse a pincode/zipcode filter value into unique trimmed codes.
     * Supports a single value or comma-separated values.
     *
     * @param  mixed  $input
     * @return array
     */
    public static function parseDeliveryPincodes($input)
    {
        if ($input === null) {
            return array();
        }

        $pincodes = array();
        foreach (explode(',', (string) $input) as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }
            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-]{0,19}$/', $token)) {
                continue;
            }
            if (!in_array($token, $pincodes, true)) {
                $pincodes[] = $token;
            }
        }

        return $pincodes;
    }

    /**
     * Filter orders by delivery address postal_code stored in shipping_address JSON.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $pincodes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDeliveryPincodeIn($query, array $pincodes)
    {
        if (empty($pincodes)) {
            return $query->whereIn('orders.id', array());
        }

        return $query->whereIn('shipping_address->postal_code', $pincodes);
    }
}
