<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ translate('All Orders') }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: {{ $font_family }};
            font-size: 9px;
            color: #222;
            direction: {{ $direction }};
            text-align: {{ $text_align }};
        }
        h2 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }
        .meta {
            margin-bottom: 10px;
            color: #555;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #c5c5c5;
            padding: 4px 5px;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background: #eceff4;
            font-weight: bold;
            text-align: {{ $text_align }};
        }
        td.qty, td.amount {
            text-align: {{ $not_text_align }};
            white-space: nowrap;
        }
        .products {
            font-size: 8px;
            line-height: 1.35;
        }
        .footer {
            margin-top: 8px;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>{{ translate('All Orders') }}</h2>
    <div class="meta">
        {{ translate('Generated at') }}: {{ date('Y-m-d H:i') }}
        &nbsp;|&nbsp;
        {{ translate('Total records') }}: {{ $orders->count() }}
        @if(!empty($filter_summary))
            &nbsp;|&nbsp; {{ translate('Filters') }}: {{ $filter_summary }}
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th width="8%">{{ translate('Order ID') }}</th>
                <th width="8%">{{ translate('Order Date') }}</th>
                <th width="9%">{{ translate('Customer Name') }}</th>
                <th width="8%">{{ translate('Customer Contact') }}</th>
                <th width="16%">{{ translate('Products') }}</th>
                <th width="5%">{{ translate('Qty') }}</th>
                <th width="7%">{{ translate('Amount') }}</th>
                <th width="7%">{{ translate('Payment Status') }}</th>
                <th width="7%">{{ translate('Order Status') }}</th>
                <th width="7%">{{ translate('Delivery Status') }}</th>
                <th width="12%">{{ translate('Delivery Address') }}</th>
                <th width="6%">{{ translate('Pincode') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php $row = \App\Exports\OrderExportPresenter::fromOrder($order); @endphp
                <tr>
                    <td>{{ $row['code'] }}</td>
                    <td>{{ $row['order_date'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['customer_contact'] }}</td>
                    <td class="products">{!! nl2br(e($row['products'])) !!}</td>
                    <td class="qty">{{ $row['quantity'] }}</td>
                    <td class="amount">{{ $row['amount'] }}</td>
                    <td>{{ $row['payment_status'] }}</td>
                    <td>{{ $row['order_status'] }}</td>
                    <td>{{ $row['delivery_status'] }}</td>
                    <td>{{ $row['delivery_address'] }}</td>
                    <td>{{ $row['postal_code'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">{{ translate('No orders found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">{{ get_setting('site_name') }}</div>
</body>
</html>
