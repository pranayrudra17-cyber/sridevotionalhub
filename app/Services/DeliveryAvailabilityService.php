<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Product;

class DeliveryAvailabilityService
{
    public function normalizePincode($pincode)
    {
        return trim((string) $pincode);
    }

    public function getServiceablePincodes()
    {
        return $this->parseConfiguredPincodes(get_setting('serviceable_pincodes'));
    }

    public function isRestrictionEnabled()
    {
        return count($this->getServiceablePincodes()) > 0;
    }

    public function isServiceable($pincode)
    {
        if (!$this->isRestrictionEnabled()) {
            return true;
        }

        $enteredPincode = $this->normalizePincode($pincode);
        if ($enteredPincode === '') {
            return false;
        }

        return in_array($enteredPincode, $this->getServiceablePincodes(), true);
    }

    public function unavailableMessage()
    {
        return translate('Sorry, delivery is not available at this pincode.');
    }

    public function availableMessage()
    {
        return translate('Delivery is available at this pincode.');
    }

    public function productRequiresPincodeCheck($product)
    {
        return $product != null && (int) $product->pincode_restriction === 1;
    }

    public function cartHasRestrictedDeliveryItems($carts)
    {
        if ($carts === null) {
            return false;
        }

        foreach ($carts as $cartItem) {
            $productId = is_array($cartItem)
                ? (isset($cartItem['product_id']) ? $cartItem['product_id'] : null)
                : (isset($cartItem->product_id) ? $cartItem->product_id : null);

            if (empty($productId)) {
                continue;
            }

            $product = Product::find($productId);
            if (!$this->productRequiresPincodeCheck($product)) {
                continue;
            }

            $shippingType = is_array($cartItem)
                ? (isset($cartItem['shipping_type']) ? $cartItem['shipping_type'] : null)
                : (isset($cartItem->shipping_type) ? $cartItem->shipping_type : null);

            if ($shippingType != 'pickup_point') {
                return true;
            }
        }

        return false;
    }

    public function getDeliveryPincodeFromCarts($carts)
    {
        if ($carts === null) {
            return '';
        }

        $firstCart = is_object($carts) && method_exists($carts, 'first') ? $carts->first() : null;
        if ($firstCart == null) {
            foreach ($carts as $cartItem) {
                $firstCart = $cartItem;
                break;
            }
        }

        if ($firstCart == null) {
            return '';
        }

        $addressId = is_array($firstCart)
            ? (isset($firstCart['address_id']) ? $firstCart['address_id'] : null)
            : (isset($firstCart->address_id) ? $firstCart->address_id : null);

        if (empty($addressId)) {
            return '';
        }

        $address = Address::find($addressId);
        if ($address == null) {
            return '';
        }

        return $this->normalizePincode($address->postal_code);
    }

    public function validateCartForDelivery($carts)
    {
        if (!$this->isRestrictionEnabled()) {
            return true;
        }

        if (!$this->cartHasRestrictedDeliveryItems($carts)) {
            return true;
        }

        return $this->isServiceable($this->getDeliveryPincodeFromCarts($carts));
    }

    public function checkResponse($pincode)
    {
        $serviceable = $this->isServiceable($pincode);

        return array(
            'success' => true,
            'result' => true,
            'serviceable' => $serviceable,
            'message' => $serviceable ? $this->availableMessage() : $this->unavailableMessage(),
        );
    }

    protected function parseConfiguredPincodes($configured)
    {
        if ($configured === null || $configured === '') {
            return array();
        }

        if (is_array($configured)) {
            $list = $configured;
        } else {
            $value = trim((string) $configured);
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $list = $decoded;
            } else {
                $value = trim($value, "[] \t\n\r\0\x0B");
                $list = preg_split('/[\s,]+/', $value);
            }
        }

        $normalized = array();
        foreach ($list as $pincode) {
            $pincode = $this->normalizePincode($pincode);
            if ($pincode !== '' && !in_array($pincode, $normalized, true)) {
                $normalized[] = $pincode;
            }
        }

        return $normalized;
    }
}
