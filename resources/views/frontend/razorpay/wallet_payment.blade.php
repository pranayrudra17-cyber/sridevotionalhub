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
        var options = {
            "key": "{{ env('RAZOR_KEY') }}", // Enter the Key ID generated from the Dashboard
            "amount": "{{$amount * 100}}", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
            "buttontext": "",
            "name": "{{ env('APP_NAME') }}", //your business name
            "description": "Wallet Payment",
            "image": "{{ uploaded_asset(get_setting('site_icon')) }}",
            "callback_url": "{{ route('api.razorpay.payment') }}",
            "prefill": { //We recommend using the prefill parameter to auto-fill customer's contact information especially their phone number
                "name": "{{ $user->name }}", //your customer's name
                "email": "{{ $use->email ?? '' }}",
                // "contact": "9000090000" //Provide the customer's phone number for better conversion rates 
            },
            "notes": {
                "user_id": "{{ $user->id }}"
            },
            "theme": {
                "color": "{{ get_setting('base_color') }}"
            },
            "modal": {
                "ondismiss": function(){
                    window.location = "{{ route('api.razorpay.cancel') }}";
                }
            }
        };
        var rzp1 = new Razorpay(options);   

        $(document).ready(function(e) {
            rzp1.open();
            // e.preventDefault();
        });
        $('#modal-close').click(function(){
            window.location = "{{ route('api.razorpay.cancel') }}";
        });
    </script>
</body>
</html>