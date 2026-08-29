@if(count($combinations[0]) > 0)
<table class="table table-bordered aiz-table">
    <thead>
        <tr>
            <td class="text-center w-100px">
                {{translate('Variant')}}
            </td>
            <td class="text-center w-150px">
                {{translate('Variant Price')}}
            </td>
            <td class="text-center" data-breakpoints="lg">
                {{translate('Description')}}
            </td>
        </tr>
    </thead>
    <tbody>

        @foreach ($combinations as $key => $combination)
            @php
                $variation_available = false;
                $sku = '';
                foreach (explode(' ', $product_name) as $key => $value) {
                    $sku .= substr($value, 0, 1);
                }

                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ) {
                        $str .= '-'.str_replace(' ', '', $item);
                        $sku .='-'.str_replace(' ', '', $item);
                    }
                    else {
                        if($colors_active == 1) {
                            $color_name = \App\Models\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                            $sku .='-'.$color_name;
                        }
                        else {
                            $str .= str_replace(' ', '', $item);
                            $sku .='-'.str_replace(' ', '', $item);
                        }
                    }
                    $stock = $product->stocks->where('variant', $str)->first();
                    // if($stock != null) {
                    //     $variation_available = true;
                    // }
                }
            @endphp
            @if(strlen($str) > 0)
            <tr class="variant">
                <td>
                    <label for="" class="control-label">{{ $str }}</label>
                </td>
                <td>
                    <input type="number" lang="en" name="price_{{ $str }}" value="@php
                            if ($product->unit_price == $unit_price) {
                                if($stock != null){
                                    echo $stock->price;
                                }
                                else {
                                    echo $unit_price;
                                }
                            }
                            else{
                                echo $unit_price;
                            }
                           @endphp" min="0" step="0.01" class="form-control" required>
                        <input type="hidden" name="sku_{{ $str }}" value="{{ $str }}">
                        <input type="hidden" lang="en" name="qty_{{ $str }}" value="10000">
                </td>
                <td>
                    <textarea name="desc_{{ $str }}" class="form-control">
                        @php
                        if($stock != null) {
                            echo $stock->description;
                        }
                        @endphp
                    </textarea>
                </td>
            </tr>
            @endif
        @endforeach

    </tbody>
</table>
@endif
