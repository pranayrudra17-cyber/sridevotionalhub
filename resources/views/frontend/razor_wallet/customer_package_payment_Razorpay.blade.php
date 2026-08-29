@extends('frontend.layouts.app')

@section('content')
    @php
        $customer_package = \App\Models\CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
    @endphp
@endsection

@section('script')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ env('RAZOR_KEY') }}", // Enter the Key ID generated from the Dashboard
        "amount": "{{$customer_package->amount*100}}", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
        "currency": "INR",
        "buttontext": "",
        "name": "{{ env('APP_NAME') }}", //your business name
        "description": "Classified Package Payment",
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
                window.location = "{{ route('customer_packages_list_show') }}";
            }
        }
    };
    var rzp1 = new Razorpay(options);   

    $(document).ready(function(e) {
        rzp1.open();
        // e.preventDefault();
    });
    $('#modal-close').click(function(){
        window.location = "{{ route('customer_packages_list_show') }}";
    });
</script>
@endsection
