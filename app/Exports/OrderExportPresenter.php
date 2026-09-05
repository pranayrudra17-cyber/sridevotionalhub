<?php

namespace App\Exports;

use App\Models\Order;

class OrderExportPresenter
{
    /**
     * Build a consistent export row from an order and its items.
     *
     * @param  \App\Models\Order  $order
     * @return array
     */
    public static function fromOrder(Order $order)
    {
        $shipping = json_decode($order->shipping_address);

        $customerName = '';
        if ($order->user != null) {
            $customerName = $order->user->name;
        } elseif ($shipping && !empty($shipping->name)) {
            $customerName = $shipping->name;
        } else {
            $customerName = 'Guest' . ($order->guest_id ? ' (' . $order->guest_id . ')' : '');
        }

        $contact = '';
        if ($shipping && !empty($shipping->phone)) {
            $contact = $shipping->phone;
        } elseif ($order->user != null && !empty($order->user->phone)) {
            $contact = $order->user->phone;
        }

        $addressParts = array();
        $postalCode = '';
        if ($shipping) {
            if (!empty($shipping->address)) {
                $addressParts[] = $shipping->address;
            }
            if (!empty($shipping->city)) {
                $addressParts[] = $shipping->city;
            }
            if (!empty($shipping->state)) {
                $addressParts[] = $shipping->state;
            }
            if (!empty($shipping->country)) {
                $addressParts[] = $shipping->country;
            }
            if (!empty($shipping->postal_code)) {
                $postalCode = $shipping->postal_code;
            }
        }

        $productLines = array();
        $quantity = 0;
        foreach ($order->orderDetails as $detail) {
            $productName = ($detail->product != null)
                ? $detail->product->getTranslation('name')
                : translate('Unknown Product');
            if (!empty($detail->variation)) {
                $productName .= ' (' . $detail->variation . ')';
            }
            $qty = (int) $detail->quantity;
            $quantity += $qty;
            $productLines[] = $productName . ' x' . $qty;
        }

        $orderDate = '';
        if (!empty($order->date)) {
            $orderDate = date('Y-m-d H:i', $order->date);
        } elseif ($order->created_at) {
            $orderDate = $order->created_at->format('Y-m-d H:i');
        }

        $statusLabel = translate(ucfirst(str_replace('_', ' ', $order->delivery_status)));
        $paymentStatus = $order->payment_status == 'paid' ? translate('Paid') : translate('Unpaid');

        return array(
            'code' => $order->code,
            'order_date' => $orderDate,
            'customer_name' => $customerName,
            'customer_contact' => $contact,
            'products' => implode("\n", $productLines),
            'products_inline' => implode('; ', $productLines),
            'quantity' => $quantity,
            'amount' => single_price($order->grand_total),
            'payment_status' => $paymentStatus,
            'order_status' => $statusLabel,
            'delivery_status' => $statusLabel,
            'delivery_address' => implode(', ', $addressParts),
            'postal_code' => $postalCode,
        );
    }
}
