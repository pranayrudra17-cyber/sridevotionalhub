@extends('frontend.layouts.app')

@section('content')
    @php
        $seller_package = \App\Models\SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
    @endphp
@endsection

@section('script')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ env('RAZOR_KEY') }}",
        "amount": "{{ (int) round($seller_package->amount * 100) }}",
        "currency": "INR",
        "buttontext": "",
        "name": "{{ env('APP_NAME') }}",
        "description": "Classified Package Payment",
        "image": "{{ uploaded_asset(get_setting('site_icon')) }}",
        "order_id": "{{ $razorpayOrder['id'] }}",
        "callback_url": "{{ route('payment.rozer') }}",
        "prefill": {
            "name": "{{ Auth::user()->name }}",
            "email": "{{ Auth::user()->email ?? '' }}"
        },
        "notes": {
            "user_id": "{{ auth()->id() }}",
            "payment_type": "seller_package_payment"
        },
        "theme": {
            "color": "{{ get_setting('base_color') }}"
        },
        "modal": {
            "ondismiss": function(){
                window.location = "{{ route('seller.packages_payment_list') }}";
            }
        },
        "handler": function (response) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('payment.rozer') }}";
            ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'].forEach(function (key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = response[key];
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.on('payment.failed', function (response) {
        var desc = (response.error && response.error.description) ? response.error.description : 'failed';
        window.location = "{{ route('payment.rozer.fail') }}?reason=" + encodeURIComponent(desc);
    });

    $(document).ready(function(e) {
        rzp1.open();
    });
    $('#modal-close').click(function(){
        window.location = "{{ route('seller.packages_payment_list') }}";
    });
</script>
@endsection
