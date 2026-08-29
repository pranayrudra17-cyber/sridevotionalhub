<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/custom-style.css') }}">
    <style>
        .border {
            border: none !important;
        }
        .card {
            -webkit-box-shadow none !important;
            box-shadow: none !important;
        }
        .card-header {
            padding: 0 !important;
        }
        .card .card-body {
            padding: 0 !important;
            border-radius: 0 !important;
        }
        .tracking-item {
            margin-left: 2rem;
        }
    </style>
</head>
<body>
    @php $trackingDetails = ((get_setting('shipment') == 1) && ($order->tracking_code))?get_shipment_tracking_details($order->code)[0]['tracking_data'] ?? null : null; @endphp
    @if(!empty($trackingDetails))
        <section class="mb-4">
            <div class="container-fluid p-0">
                <div class="col-md-12 col-sm-12">
                    @include('frontend.partials.shipment_tracking_info')
                </div>
            </div>
        </section>
    @endif
</body>
</html>