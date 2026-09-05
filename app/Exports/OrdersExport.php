<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithMapping, WithHeadings, WithCustomChunkSize, ShouldAutoSize
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return array(
            translate('Order ID'),
            translate('Order Date'),
            translate('Customer Name'),
            translate('Customer Contact'),
            translate('Products'),
            translate('Quantity'),
            translate('Amount'),
            translate('Payment Status'),
            translate('Order Status'),
            translate('Delivery Status'),
            translate('Delivery Address'),
            translate('Pincode/Zipcode'),
        );
    }

    /**
     * @param  \App\Models\Order  $order
     * @return array
     */
    public function map($order): array
    {
        $row = OrderExportPresenter::fromOrder($order);

        return array(
            $row['code'],
            $row['order_date'],
            $row['customer_name'],
            $row['customer_contact'],
            $row['products_inline'],
            $row['quantity'],
            $row['amount'],
            $row['payment_status'],
            $row['order_status'],
            $row['delivery_status'],
            $row['delivery_address'],
            $row['postal_code'],
        );
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }
}
