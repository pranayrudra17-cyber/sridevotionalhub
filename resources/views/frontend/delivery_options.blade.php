@php
    $user = auth()->user();
@endphp
<hr>
<div class="row no-gutters mb-3">
    <div class="col-12">
        <div class="d-flex gap-6 mt-2 lg:border-t lg:border-gray-200 items-start">
            <div class="d-flex flex-col gap-2 text-center justify-center items-center w-[62px]"><img class="w-8 h-8"
                    src="{{ uploaded_asset(226) }}" alt="Piracy-free">
                <div>
                    <div class=" w-[62px] text-secondary-black font-normal text-small tracking-normal font-manrope">Piracy-free
                    </div>
                </div>
            </div>
            <div class="d-flex flex-col gap-2 text-center justify-center items-center w-[62px]"><img class="w-8 h-8"
                    src="{{ uploaded_asset(230) }}" alt="Assured Quality">
                <div>
                    <div class=" w-[62px] text-secondary-black font-normal text-small tracking-normal font-manrope">Assured
                        Quality</div>
                </div>
            </div>
            <div class="d-flex flex-col gap-2 text-center justify-center items-center w-[62px]"><img class="w-8 h-8"
                    src="{{ uploaded_asset(228) }}" alt="Secure Transactions">
                <div>
                    <div class=" w-[62px] text-secondary-black font-normal text-small tracking-normal font-manrope">Secure
                        Transactions</div>
                </div>
            </div>
            <div class="d-flex flex-col gap-2 text-center justify-center items-center w-[62px]"><img class="w-8 h-8"
                    src="{{ uploaded_asset(227) }}" alt="Fast Delivery">
                <div>
                    <div class=" w-[62px] text-secondary-black font-normal text-small tracking-normal font-manrope">Fast
                        Delivery</div>
                </div>
            </div>
            <div class="d-flex flex-col gap-2 text-center justify-center items-center w-[62px]"><img class="w-8 h-8"
                    src="{{ uploaded_asset(229) }}" alt="Sustainably Printed">
                <div>
                    <div class=" w-[62px] text-secondary-black font-normal text-small tracking-normal font-manrope">Sustainably
                        Printed</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 text-left" id="shipping_etd" @if(isCustomer() && !empty(user_default_address())) style="display:none;" @endif>
        <div class="col-md-12 col-sm-12 p-0 no-gutters">
            <div class="delivery-box">
                <h2 class="mt-4 mb-2 fs-18 fw-700 text-dark">{{ translate('Delivery Options') }}</h2>
                <div class="d-flex position-relative align-items-center">
                    <div class="col-md-6 p-0 delivery-input-box">
                        <input type="text" class="border border-soft-light form-control fs-14 hov-animate-outline" minlength="6" maxlength="6"
                            id="delivery_pincode" pattern="[123456789][0-9]{5}" name="pincode" placeholder="{{ translate('Pincode') }}" value="{{ (isCustomer() && !empty(user_default_address()))?user_default_address()->postal_code:'' }}" autocomplete="off">
                        <button type="button" id="delivery_check" class="btn btn-secondary rounded-0 w-100 disabled" disabled="disabled">{{ translate('Check') }}</button>
                    </div>
                </div>
                <p class="pl-2 fw-100">{{ translate('Please enter pincode to check delivery time.') }}</p>
            </div>
            <div class="delivery-result" style="display: none;">
                <div class="d-flex items-center gap-2 h-25px mt-4 fs-20">
                    <div>
                        <i class="la la-map-marker text-secondary-base"></i>
                    </div>
                    <div>
                        <p class="text-neutral-700 font-normal text-base tracking-normal font-manrope mt-3 delivered-pincode"></p>
                    </div>
                    <div>
                        <p class="cursor-pointer false text-primary-default font-bold text-base tracking-normal font-manrope text-primary mt-3 pincode-change">{{ translate('Change') }}</p>
                    </div>
                </div>
                <div class="mt-2 fw-200 font-manrope fs-16">{{ translate('The delivery is estimated on or before') }} <span class="font-bold fw-800 delivered-date"> </span> {{ translate('for this order.') }}</div>
            </div>
        </div>
    </div>
</div>

@section('script')
    <script type="text/javascript">
        @if(isCustomer() && !empty(user_default_address()))
        $(document).ready(function() {
            if($('#delivery_pincode').val().length == 6){
                get_etd();    
                $('#delivery_check').removeAttr('disabled');
                $('#delivery_check').removeClass('disabled');
                $('#delivery_check').removeClass('btn-secondary');
                $('#delivery_check').addClass('btn-primary');
            }
            else{
                $('#delivery_check').attr('disabled','disabled');
                $('#delivery_check').addClass('disabled');
                $('#delivery_check').removeClass('btn-primary');
                $('#delivery_check').addClass('btn-secondary');
            }
            setTimeout(function() { $('#shipping_etd').css('display','block') }, 2000);
        });
        @endif
        $(document).on("keyup", '#delivery_pincode', function(event){
            this.value = this.value.replace(/[^\d]/,'');
            if(this.value.length == 6){
                $('#delivery_check').removeAttr('disabled');
                $('#delivery_check').removeClass('disabled');
                $('#delivery_check').removeClass('btn-secondary');
                $('#delivery_check').addClass('btn-primary');
            }
            else{
                $('#delivery_check').attr('disabled','disabled');
                $('#delivery_check').addClass('disabled');
                $('#delivery_check').removeClass('btn-primary');
                $('#delivery_check').addClass('btn-secondary');
            }
        });
        $(document).on("click", '#delivery_check', function(event){
            get_etd();
        });
        function get_etd(){
            var dis = $('#delivery_check');
            var disText = "Check";
            dis.attr('disabled','disabled');
            dis.addClass('disabled');
            dis.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            var quantity = $('#option-choice-form input[name=quantity]').val();
            var pincode = $('#delivery_pincode').val();
            if(quantity > 0 && checkAddToCartValidity() && (pincode.length == '6')){
                var postData = $('#option-choice-form').serializeArray();
                postData.push({ name: 'pincode', value: pincode });
                $.post("{{ route('shipment.pincode') }}", postData, function(data){
                    if(data.result){
                        $('.delivery-box').css('display','none');
                        $('.delivery-result').css('display','block');
                        $('.delivered-pincode').text(pincode);
                        $('.delivered-date').text(data.message);
                    }
                    else
                        AIZ.plugins.notify('warning', data.message);
                    dis.removeAttr('disabled');
                    dis.removeClass('disabled');
                    dis.html(disText);
                });
            }
            else
            {
                dis.removeAttr('disabled');
                dis.removeClass('disabled');
                dis.html(disText);
            }
        }
        $(document).on("click", '.pincode-change', function(event){
            $('.delivery-box').css('display','block');
            $('.delivery-result').css('display','none');
            $('.delivered-pincode').text('');
            $('.delivered-date').text('');
        });
    </script>
@endsection