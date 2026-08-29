@extends('frontend.layouts.app')

@section('content')

@endsection

@section('script')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ env('RAZOR_KEY') }}", // Enter the Key ID generated from the Dashboard
        "amount": "{{round($combined_order->grand_total) * 100}}", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
        "currency": "INR",
        "name": "{{ env('APP_NAME') }}", //your business name
        "description": "Cart Payment",
        "image": "{{ uploaded_asset(get_setting('site_icon')) }}",
        "callback_url": "{{ route('payment.rozer') }}",
        "prefill": { //We recommend using the prefill parameter to auto-fill customer's contact information especially their phone number
            "name": "{{ Auth::user()->name }}", //your customer's name
            "email": "{{ Auth::user()->email ?? '' }}",
            // "contact": "9000090000" //Provide the customer's phone number for better conversion rates 
        },
        "notes": {
            "user_id": "{{ auth()->id() }}"
        },
        "theme": {
            "color": "{{ get_setting('base_color') }}"
        },
        "modal": {
            "ondismiss": function(){
                window.location = "{{ route('purchase_history.details', encrypt($combined_order->id)) }}";
            }
        }
    };
    var rzp1 = new Razorpay(options);   

    $(document).ready(function(e) {
        rzp1.open();
        // e.preventDefault();
    });
    $('#modal-close').click(function(){
        window.location = "{{ route('purchase_history.details', encrypt($combined_order->id)) }}";
    });
</script>
@endsection
