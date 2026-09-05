<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'set_default',
        'street_house_no',
        'nearby_landmark',
        'area_locality',
        'additional_address_details',
    ];

    /**
     * Save each address-detail field to its own column and keep a combined value in `address`.
     */
    public function applyDetailFieldsFromRequest($request)
    {
        $this->street_house_no = $this->trimmedInput($request->input('street_house_no'));
        $this->nearby_landmark = $this->trimmedInput($request->input('nearby_landmark'));
        $this->area_locality = $this->trimmedInput($request->input('area_locality'));
        $this->additional_address_details = $this->trimmedInput($request->input('additional_address_details'));
        $this->address = self::combinedAddressFromRequest($request);
    }

    /**
     * Combine detailed address inputs into the existing address column value.
     */
    public static function combinedAddressFromRequest($request)
    {
        $parts = [
            $request->input('address'),
            $request->input('street_house_no'),
            $request->input('nearby_landmark'),
            $request->input('area_locality'),
            $request->input('additional_address_details'),
        ];

        $parts = array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, $parts), function ($value) {
            return $value !== '';
        });

        return implode(', ', $parts);
    }

    protected function trimmedInput($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
