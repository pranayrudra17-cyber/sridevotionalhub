<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/custom-style.css') }}">
</head>
<body>
    <section class="py-4 mb-4 bg-light">
        <div class="container text-center"></div>
    </section>

    <!-- SCRIPTS -->
    <script src="{{ static_asset('assets/js/vendors.js') }}"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var extraFields = {
            "payment_type": "{{ request('payment_type', 'wallet_payment') }}",
            "user_id": "{{ $user->id }}",
            "amount": "{{ $amount }}",
            "package_id": "{{ $package_id ?? 0 }}"
        };
        var options = {
            "key": "{{ env('RAZOR_KEY') }}",
            "amount": "{{ (int) round($amount * 100) }}",
            "buttontext": "",
            "name": "{{ env('APP_NAME') }}",
            "description": "Wallet Payment",
            "image": "{{ uploaded_asset(get_setting('site_icon')) }}",
            "order_id": "{{ $razorpayOrder['id'] }}",
            "callback_url": "{{ route('api.razorpay.payment') }}",
            "prefill": {
                "name": "{{ $user->name }}",
                "email": "{{ $user->email ?? '' }}"
            },
            "notes": {
                "user_id": "{{ $user->id }}",
                "payment_type": "{{ request('payment_type', 'wallet_payment') }}"
            },
            "theme": {
                "color": "{{ get_setting('base_color') }}"
            },
            "modal": {
                "ondismiss": function(){
                    window.location = "{{ route('api.razorpay.cancel') }}";
                }
            },
            "handler": function (response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ url('api/v2/razorpay/payment') }}";
                ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'].forEach(function (key) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = response[key];
                    form.appendChild(input);
                });
                Object.keys(extraFields).forEach(function (key) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = extraFields[key];
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function () {
            window.location = "{{ route('api.razorpay.cancel') }}";
        });

        $(document).ready(function(e) {
            rzp1.open();
        });
        $('#modal-close').click(function(){
            window.location = "{{ route('api.razorpay.cancel') }}";
        });
    </script>
</body>
</html>
