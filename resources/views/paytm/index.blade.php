@extends('backend.layouts.app')

@section('content')

<div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 h6 text-center">{{translate('Paytm Activation')}}</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="clearfix">
                            <img class="float-left" src="{{ static_asset('assets/img/cards/paytm.jpg') }}" height="30">
                            <label class="aiz-switch aiz-switch-success mb-0 float-right">
                                <input type="checkbox" onchange="updateSettings(this, 'paytm_payment')" <?php if(\App\Models\BusinessSetting::where('type', 'paytm_payment')->first()->value == 1) echo "checked";?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 h6 text-center">{{translate('ToyyibPay Activation')}}</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="clearfix">
                            <img class="float-left" src="{{ static_asset('assets/img/cards/toyyibpay.png') }}" height="30">
                            <label class="aiz-switch aiz-switch-success mb-0 float-right">
                                <input type="checkbox" onchange="updateSettings(this, 'toyyibpay_payment')" <?php if(\App\Models\BusinessSetting::where('type', 'toyyibpay_payment')->first()->value == 1) echo "checked";?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
    </div>


<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Paytm Credential')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('paytm.update_credentials') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_ENVIRONMENT">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM ENVIRONMENT')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_ENVIRONMENT" value="{{  env('PAYTM_ENVIRONMENT') }}" placeholder="local or production" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_MERCHANT_ID">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM MERCHANT ID')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_MERCHANT_ID" value="{{  env('PAYTM_MERCHANT_ID') }}" placeholder="PAYTM MERCHANT ID" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_MERCHANT_KEY">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM MERCHANT KEY')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_MERCHANT_KEY" value="{{  env('PAYTM_MERCHANT_KEY') }}" placeholder="PAYTM MERCHANT KEY" >
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_MERCHANT_WEBSITE">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM MERCHANT WEBSITE')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_MERCHANT_WEBSITE" value="{{  env('PAYTM_MERCHANT_WEBSITE') }}" placeholder="PAYTM MERCHANT WEBSITE" >
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_CHANNEL">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM CHANNEL')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_CHANNEL" value="{{  env('PAYTM_CHANNEL') }}" placeholder="PAYTM CHANNEL" >
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="PAYTM_INDUSTRY_TYPE">
                        <div class="col-lg-4">
                            <label class="col-from-label">{{translate('PAYTM INDUSTRY TYPE')}}</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="PAYTM_INDUSTRY_TYPE" value="{{  env('PAYTM_INDUSTRY_TYPE') }}" placeholder="PAYTM INDUSTRY TYPE" >
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('ToyyibPay Credential')}}</h5>
            </div>
            <div class="card-body">
                    <form class="form-horizontal" action="{{ route( 'payment_method.update' ) }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="toyyibpay">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TOYYIBPAY_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('TOYYIBPAY KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                            <input type="text" class="form-control" name="TOYYIBPAY_KEY" value="{{  env('TOYYIBPAY_KEY') }}" placeholder="{{ translate('TOYYIBPAY KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TOYYIBPAY_CATEGORY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('TOYYIBPAY CATEGORY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="TOYYIBPAY_CATEGORY" value="{{  env('TOYYIBPAY_CATEGORY') }}" placeholder="{{ translate('TOYYIBPAY CATEGORY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('ToyyibPay Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="toyyibpay_sandbox" type="checkbox" @if (get_setting('toyyibpay_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }
            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', 'Settings updated successfully');
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection