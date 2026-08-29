@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h5">{{ translate('Edit Pooja') }}</h5>
    </div>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form class="form form-horizontal mar-top" action="{{ route('poojaproducts.update', $product->id) }}"
                method="POST" enctype="multipart/form-data" id="choice_form">
                <input name="_method" type="hidden" value="POST">
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input type="hidden" value="10000" name="current_stock">
                @csrf
                <div class="card">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-light">
                            @foreach (\App\Models\Language::all() as $key => $language)
                                <li class="nav-item">
                                    <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3"
                                        href="{{ route('poojaproducts.edit', ['id' => $product->id, 'lang' => $language->code]) }}">
                                        <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}"
                                            height="11" class="mr-1">
                                        <span>{{ $language->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('General') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Product Name') }}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="name"
                                    placeholder="{{ translate('Product Name') }}"
                                    value="{{ $product->getTranslation('name', $lang) }}" required>
                            </div>
                        </div>
                        <div class="form-group row" id="category">
                            <label class="col-lg-2 col-from-label">{{ translate('Category') }}</label>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" name="category_id" id="category_id"
                                    data-selected="{{ $product->category_id }}" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}
                                        </option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Product File') }}</label>
                            <div class="col-lg-8">
                                <div class="input-group" data-toggle="aizuploader" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="file" class="selected-files"
                                        value="{{ $product->file_name }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Tags') }}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags"
                                    value="{{ $product->tags }}" placeholder="{{ translate('Type to add a tag') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Images') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                for="signinSrEmail">{{ translate('Main Images') }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="photos" value="{{ $product->photos }}"
                                        class="selected-files" required>
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="signinSrEmail">{{ translate('Thumbnail Image') }}
                                <small>(290x300)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}"
                                        class="selected-files" required>
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Meta Tags') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Meta Title') }}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="meta_title"
                                    value="{{ $product->meta_title }}" placeholder="{{ translate('Meta Title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Description') }}</label>
                            <div class="col-lg-8">
                                <textarea name="meta_description" rows="8" class="form-control">{{ $product->meta_description }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                for="signinSrEmail">{{ translate('Meta Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image"
                                    data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="meta_img" value="{{ $product->meta_img }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">{{ translate('Slug') }}</label>
                            <div class="col-lg-8">
                                <input type="text" placeholder="{{ translate('Slug') }}" id="slug"
                                    name="slug" value="{{ $product->slug }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Variation')}}</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" value="0" name="colors_active" >
                        
                        <div class="form-group row gutters-5">
                            <div class="col-lg-3">
                                <input type="text" class="form-control" value="{{translate('Attributes')}}" disabled>
                            </div>
                            <div class="col-lg-8">
                                <select name="choice_attributes[]" id="choice_attributes" data-selected-text-format="count" data-live-search="true" class="form-control aiz-selectpicker" multiple data-placeholder="{{ translate('Choose Attributes') }}">
                                    @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                        @if($attribute->id == 3)
                                            <option value="{{ $attribute->id }}" @if($product->attributes != null && in_array($attribute->id, json_decode($product->attributes, true))) selected @endif>{{ $attribute->getTranslation('name') }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="">
                            <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}</p>
                            <br>
                        </div>

                        <div class="customer_choice_options" id="customer_choice_options">
                            @if($product->choice_options != null)
                                @foreach (json_decode($product->choice_options) as $key => $choice_option)
                                <div class="form-group row">
                                    <div class="col-lg-3">
                                        <input type="hidden" name="choice_no[]" value="{{ $choice_option->attribute_id }}">
                                        <input type="text" class="form-control" name="choice[]" value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}" placeholder="{{ translate('Choice Title') }}" disabled>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_{{ $choice_option->attribute_id }}[]" multiple>
                                            @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                            <option value="{{ $row->value }}" @if( in_array($row->value, $choice_option->values)) selected @endif>
                                                {{ $row->value }}
                                            </option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="text" class="form-control aiz-tag-input" name="choice_options_{{ $choice_option->attribute_id }}[]" placeholder="{{ translate('Enter choice values') }}" value="{{ implode(',', $choice_option->values) }}" data-on-change="update_sku"> --}}
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Price') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Unit price') }}</label>
                            <div class="col-lg-8">
                                <input type="text" placeholder="{{ translate('Unit price') }}" name="unit_price"
                                    class="form-control" value="{{ $product->unit_price }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Purchase price') }}</label>
                            <div class="col-lg-8">
                                <input type="number" lang="en" min="0" step="0.01"
                                    placeholder="{{ translate('Purchase price') }}" name="purchase_price"
                                    class="form-control" value="{{ $product->purchase_price }}" required>
                            </div>
                        </div>

                        @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                            @php
                                $tax_amount = 0;
                                $tax_type = '';
                                foreach ($tax->product_taxes as $row) {
                                    if ($product->id == $row->product_id) {
                                        $tax_amount = $row->tax;
                                        $tax_type = $row->tax_type;
                                    }
                                }
                            @endphp
                            <div class="form-group row">
                                <label class="col-lg-2 col-from-label">
                                    {{$tax->name}}
                                </label>
                                <div class="col-lg-6">
                                    <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                    <input type="number" lang="en" min="0" step="0.01"
                                        placeholder="{{ translate('tax') }}" name="tax[]" class="form-control"
                                        value="{{ $tax_amount }}" required>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control aiz-selectpicker" name="tax_type[]" required>
                                        <option value="amount" @if($tax_type == 'amount') selected @endif>
                                            {{translate('Flat')}}
                                        </option>
                                        <option value="percent" @if($tax_type == 'percent') selected @endif>
                                            {{translate('Percent')}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        @php
                            $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                            $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                        @endphp

                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label"
                                for="start_date">{{ translate('Discount Date Range') }}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control aiz-date-range"
                                    value="{{ $start_date . ' to ' . $end_date }}" name="date_range"
                                    placeholder="{{ translate('Select Date') }}" data-time-picker="true"
                                    data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Discount') }}</label>
                            <div class="col-lg-6">
                                <input type="number" lang="en" min="0" step="0.01"
                                    placeholder="{{ translate('Discount') }}" name="discount" class="form-control"
                                    value="{{ $product->discount }}" required>
                            </div>
                            <div class="col-lg-2">
                                <select class="form-control aiz-selectpicker" name="discount_type" required>
                                    <option value="amount" <?php if ($product->discount_type == 'amount') {
                                        echo 'selected';
                                    } ?>>{{ translate('Flat') }}</option>
                                    <option value="percent" <?php if ($product->discount_type == 'percent') {
                                        echo 'selected';
                                    } ?>>{{ translate('Percent') }}</option>
                                </select>
                            </div>
                        </div>

                        <br>
                        <div class="sku_combination" id="sku_combination">

                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Description') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{ translate('Description') }}</label>
                            <div class="col-lg-9">
                                <textarea class="aiz-text-editor" name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Pincode / Zipcode Restriction') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-from-label">{{translate('Restrict delivery by pincode/zipcode')}}</label>
                            <div class="col-lg-9">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="pincode_restriction" value="1" @if($product->pincode_restriction == 1) checked @endif>
                                    <span></span>
                                </label>
                                <small class="text-muted d-block">{{ translate('If enabled, this product can be delivered only to serviceable pincodes.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 text-right">
                    <button type="submit" name="button"
                        class="btn btn-primary">{{ translate('Update Product') }}</button>
                </div>
        </div>
        </form>
    </div>
@endsection

@section('script')

<script type="text/javascript">

    function add_more_customer_choice_option(i, name){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route('poojaproducts.add-more-choice-option') }}',
            data:{
               attribute_id: i
            },
            success: function(data) {
                var obj = JSON.parse(data);
                $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ translate('Choice Title') }}" readonly>\
                    </div>\
                    <div class="col-md-8">\
                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i +'[]" multiple>\
                            '+obj+'\
                        </select>\
                    </div>\
                </div>');
                AIZ.plugins.bootstrapSelect('refresh');
           }
       });


    }

    $(document).on("change", ".attribute_choice",function() {
        update_sku();
    });

    function delete_row(em){
        $(em).closest('.form-group').remove();
        update_sku();
    }

    function delete_variant(em){
        $(em).closest('.variant').remove();
    }

    function update_sku(){
        $.post('{{ route('poojaproducts.sku_combination_edit') }}', $('#choice_form').serialize())
        .done(function( data ) {
            $('#sku_combination').html(data);
            setTimeout(() => {
                AIZ.uploader.previewGenerate();
            }, "500");
            AIZ.plugins.fooTable();
            if (data.length > 1) {
                $('#show-hide-div').hide();
            }
            else {
                $('#show-hide-div').show();
            }
        });
    }

    AIZ.plugins.tagify();

    $(document).ready(function(){
        update_sku();

        $('.remove-files').on('click', function(){
            $(this).parents(".col-md-4").remove();
        });
    });

    $('#choice_attributes').on('change', function() {
        $.each($("#choice_attributes option:selected"), function(j, attribute){
            flag = false;
            $('input[name="choice_no[]"]').each(function(i, choice_no) {
                if($(attribute).val() == $(choice_no).val()){
                    flag = true;
                }
            });
            if(!flag){
                add_more_customer_choice_option($(attribute).val(), $(attribute).text());
            }
        });

        var str = @php echo $product->attributes @endphp;

        $.each(str, function(index, value){
            flag = false;
            $.each($("#choice_attributes option:selected"), function(j, attribute){
                if(value == $(attribute).val()){
                    flag = true;
                }
            });
            if(!flag){
                $('input[name="choice_no[]"][value="'+value+'"]').parent().parent().remove();
            }
        });

        update_sku();
    });

</script>

@endsection

