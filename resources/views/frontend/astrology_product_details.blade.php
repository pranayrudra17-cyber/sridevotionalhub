@extends('frontend.layouts.app')
@section('meta_title'){{ $detailedProduct->meta_title }}@stop
@section('meta_description'){{ $detailedProduct->meta_description }}@stop
@section('meta_keywords'){{ $detailedProduct->tags }}@stop
@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">
    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="{{ route('astrology', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
@endsection
@section('content')
    <section class="pt-4 mb-4">
        <div class="container text-center">
            <div class="row">
                {{-- <div class="col-lg-6 text-center text-lg-left">
                    <h1 class="fw-600 h4">{{ $detailedProduct->getTranslation('name') }}</h1>
                </div> --}}
                <div class="col-lg-6 text-center text-lg-left">
                    <ul class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item opacity-50">
                            <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                        </li>
                        <li class="breadcrumb-item opacity-50">
                            <a class="text-reset" href="{{ route('astrologies.all') }}">{{ translate('Astrology')}}</a>
                        </li>
                        <li class="text-dark fw-600 breadcrumb-item">
                            <a class="text-reset" href="#">"{{ $detailedProduct->getTranslation('name') }}"</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <ul class="breadcrumb-book bg-transparent p-0 m-0 justify-content-lg-end">
                        @php $previous = get_astrology_previous_product($detailedProduct->id); $previousProduct = (get_single_product([$previous])); @endphp
                        <li class="text-dark prev-book-navigation fw-600 breadcrumb-item">
                            <a class="text-reset {{ (!$previous)?'opacity-50':''}}" href="{{ ($previous)?route('astrology', $previousProduct->first()->slug):'javascript:void(0);'}}">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" viewBox="0 0 17 17">
                                    <g>
                                    </g>
                                        <path d="M10.854 4.854l-3.647 3.646 3.646 3.646-0.707 0.707-4.353-4.353 4.354-4.354 0.707 0.708zM17 8.5c0 4.687-3.813 8.5-8.5 8.5s-8.5-3.813-8.5-8.5 3.813-8.5 8.5-8.5 8.5 3.813 8.5 8.5zM16 8.5c0-4.136-3.364-7.5-7.5-7.5s-7.5 3.364-7.5 7.5 3.364 7.5 7.5 7.5 7.5-3.364 7.5-7.5z" fill="#000000"></path>
                                </svg>
                                {{ translate('PREVIOUS') }}
                            </a>
                            @if($previous)
                                <div class="product-nav-item-content">
                                    <a class="product-nav-item-link" href="{{ route('astrology', $previousProduct->first()->slug) }}"></a>
                                    <img src="{{ uploaded_asset($previousProduct->first()->thumbnail_img) }}" alt="{{ translate('Previous Book Image') }}">
                                    <div class="product-nav-item-inner text-left">
                                        <h4 class="product-nav-item-title">{{ $previousProduct->first()->getTranslation('name') }}</h4>
                                        <span class="product-nav-item-price">
                                            @if (home_base_price($previousProduct->first()) != home_discounted_base_price($previousProduct->first()))
                                                <del aria-hidden="true">{{ home_discounted_base_price($previousProduct->first()) }}</del>
                                            @endif
                                            <ins aria-hidden="true">{{ home_base_price($previousProduct->first()) }}</ins>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </li>
                        @php $next = get_astrology_next_product($detailedProduct->id); $nextProduct = (get_single_product([$next])); @endphp
                        <li class="text-dark next-book-navigation fw-600 breadcrumb-item">
                            <a class="text-reset {{ (!$next)?'opacity-50':''}}" href="{{ ($next)?route('astrology', $nextProduct->first()->slug):'javascript:void(0);'}}">
                                {{ translate('NEXT_CAP') }}
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" viewBox="0 0 17 17">
                                    <g>
                                    </g>
                                        <path d="M6.854 4.146l4.353 4.354-4.354 4.354-0.707-0.707 3.647-3.647-3.647-3.646 0.708-0.708zM17 8.5c0 4.687-3.813 8.5-8.5 8.5s-8.5-3.813-8.5-8.5 3.813-8.5 8.5-8.5 8.5 3.813 8.5 8.5zM16 8.5c0-4.136-3.364-7.5-7.5-7.5s-7.5 3.364-7.5 7.5 3.364 7.5 7.5 7.5 7.5-3.364 7.5-7.5z" fill="#000000"></path>
                                </svg>
                            </a>
                            @if($next)
                                <div class="product-nav-item-content">
                                    <a class="product-nav-item-link" href="{{ route('astrology', $nextProduct->first()->slug) }}"></a>
                                    <div class="product-nav-item-inner">
                                        <h4 class="product-nav-item-title">{{ $nextProduct->first()->getTranslation('name') }}</h4>
                                        <span class="product-nav-item-price">
                                            @if (home_base_price($nextProduct->first()) != home_discounted_base_price($nextProduct->first()))
                                                <del aria-hidden="true">{{ home_discounted_base_price($nextProduct->first()) }}</del>
                                            @endif
                                            <ins aria-hidden="true">{{ home_base_price($nextProduct->first()) }}</ins>
                                        </span>
                                    </div>
                                    <img src="{{ uploaded_asset($nextProduct->first()->thumbnail_img) }}" alt="{{ translate('Next Book Image') }}">
                                </div>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <section class="mb-4">
        <div class="container">
            <div class="bg-white shadow-sm rounded p-3">
                <div class="row">
                    <div class="col-xl-5 col-lg-6">
                        <div class="sticky-top z-3 row gutters-10 flex-row-reverse">
                            @if($detailedProduct->photos != null)
                                @php
                                    $photos = explode(',',$detailedProduct->photos);
                                @endphp
                                <div class="col">
                                    <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb' data-fade='true'>
                                        @foreach ($photos as $key => $photo)
                                        <div class="carousel-box img-zoom rounded">
                                            <img
                                                class="img-fluid lazyload"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-auto w-90px">
                                    <div class="aiz-carousel carousel-thumb product-gallery-thumb" data-items='5' data-nav-for='.product-gallery' data-vertical='true' data-focus-select='true'>
                                        @foreach ($photos as $key => $photo)
                                        <div class="carousel-box c-pointer border p-1 rounded">
                                            <img
                                                class="lazyload mw-100 size-60px mx-auto"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->getTranslation('name') }}
                            </h1>

                            <div class="row align-items-center">
                                <div class="col-6">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp
                                    <span class="rating">
                                        {{ renderStarRating($detailedProduct->rating) }}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }} {{ translate('reviews')}})</span>
                                </div>
                                <div class="col-6 text-right">
                                </div>
                            </div>


                            <hr>

                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <a href="#" class="text-reset">{{ translate('Half hourly flat rates applied for all the astrology consultations') }}</a>
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-soft-primary" onclick="show_chat_modal()">{{ translate('Message Seller')}}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto">
                                        <img src="{{ uploaded_asset($detailedProduct->brand->logo) }}" alt="{{ $detailedProduct->brand->getTranslation('name') }}" height="30">
                                    </div>
                                @endif
                            </div>

                            <hr>

                            @if(home_price($detailedProduct) != home_discounted_price($detailedProduct))
                                
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="fs-20 opacity-60">
                                            <del>
                                                {{ home_price($detailedProduct) }}
                                                <span class="opacity-70 text-primary">/ {{ translate('30min Onwards') }}</span>
                                            </del>
                                        </div>
                                    </div>
                                </div>

                                <div class="row no-gutters mt-2">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Discount Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="">
                                            <strong class="h2 fw-600 text-primary">
                                                {{ home_discounted_price($detailedProduct) }}
                                            </strong>
                                            <span class="opacity-70 text-primary">/ {{ translate('30min Onwards') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="">
                                            <strong class="h2 fw-600 text-primary">
                                                {{ home_discounted_price($detailedProduct) }}
                                            </strong>
                                            <span class="opacity-70 text-primary">/ {{ translate('30min Onwards') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4">
                                    <div class="col-2">
                                        <div class="opacity-50">{{  translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="d-inline-block club-point bg-soft-base-1 border-light-base-1 border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>

                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Total Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="product-price">
                                            <strong id="chosen_price" class="h4 fw-600 text-primary">

                                            </strong>
                                        </div>
                                    </div>
                                </div>

                            </form>

                            <div class="mt-3">
                                <!-- <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600" onclick="addToCart()">
                                    <i class="las la-shopping-bag"></i>
                                    <span class="d-none d-md-inline-block"> {{ translate('Add to cart')}}</span>
                                </button>
                                <button type="button" class="btn btn-primary buy-now fw-600" onclick="buyNow()">
                                    <i class="la la-shopping-cart"></i> {{ translate('Buy Now')}}
                                </button> -->
                                <button type="button" class="btn btn-primary buy-now fw-600" onclick="show_book_modal()">
                                    {{ translate('Book Now')}}
                                </button>
                            </div>



                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    @if(Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if(Auth::check()){
                                                if(Auth::user()->referral_code == null){
                                                    Auth::user()->referral_code = substr(Auth::user()->id.Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product').'/'.$detailedProduct->slug."?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div class="form-group">
                                            <textarea id="referral_code_url" class="form-control" readonly type="text" style="display:none">{{$referral_code_url}}</textarea>
                                        </div>
                                        <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary" data-attrcpy="{{ translate('Copied')}}" onclick="CopyToClipboard('referral_code_url')">{{ translate('Copy the Promote Link')}}</button>
                                    @endif
                                </div>
                            </div>

                            <hr class="mt-2">

                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Refund')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank"> 
                                            @if ($refund_sticker != null) 
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36"> 
                                            @else 
                                                <img src="{{ static_asset('assets/img/refund-sticker.jpg') }}" height="36"> 
                                            @endif</a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2" target="_blank">{{ translate('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($detailedProduct->added_by == 'seller')
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="product-description-label">{{ translate('Seller Guarantees')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        @if ($detailedProduct->user->shop->verification_status == 1)
                                            {{ translate('Verified seller')}}
                                        @else
                                            {{ translate('Non verified seller')}}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="row no-gutters mt-4">
                                <div class="col-2">
                                    <div class="opacity-50 mt-2">{{ translate('Share')}}:</div>
                                </div>
                                <div class="col-10">
                                    <div class="aiz-share"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->shop != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->shop->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                            <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 "/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ translate('Sold by')}}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->shop->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                    @else
                                        <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                    @endif
                                </a>
                                <div class="location opacity-70">{{ $detailedProduct->user->shop->address }}</div>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($total > 0)
                                            {{ renderStarRating($detailedProduct->user->shop->rating) }}
                                        @else
                                            {{ renderStarRating(0) }}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $total }} {{ translate('customer reviews')}})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ translate('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank">
                                                <i class="lab la-youtube opacity-60"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white rounded shadow-sm mb-3">
                        <div class="p-3 border-bottom fs-16 fw-600">
                            {{ translate('Top Booked Astrologies')}}
                        </div>
                        <div class="p-3">
                            <ul class="list-group list-group-flush">
                                @foreach (filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->where('digital', 1)->orderBy('num_of_sale', 'desc'))->limit(6)->get() as $key => $top_product)
                                <li class="py-3 px-0 list-group-item border-light">
                                    <div class="row gutters-10 align-items-center">
                                        <div class="col-5">
                                            <a href="{{ route('astrology', $top_product->slug) }}" class="d-block text-reset">
                                                <img
                                                    class="img-fit lazyload h-110px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($top_product->thumbnail_img) }}"
                                                    alt="{{ $top_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="col-7">
                                            <h4 class="fs-13 text-truncate-2">
                                                <a href="{{ route('astrology', $top_product->slug) }}" class="d-block text-reset">{{ $top_product->getTranslation('name') }}</a>
                                            </h4>
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($top_product->rating) }}
                                            </div>
                                            <div class="mt-2">
                                                <span class="fs-17 fw-600 text-primary">{{ home_discounted_base_price($top_product) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="bg-white mb-3 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Description')}}</a>
                            @if($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Video')}}</a>
                            @endif
                            @if($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Downloads')}}</a>
                            @endif
                            <a href="#tab_default_4" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Reviews')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden">
                                        <?php echo $detailedProduct->getTranslation('description'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_default_2">
                                <div class="p-4">
                                    <!-- 16:9 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-16by9 mb-5">
                                        @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ explode('=', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                            <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab_default_3">
                                <div class="p-4 text-center">
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}"  class="btn btn-primary">{{  translate('Download') }}</a>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab_default_4">
                                <div class="p-4">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            @if($review->user != null)
                                            <li class="media list-group-item d-flex">

                                                <span class="avatar avatar-md mr-3">
                                                    <img
                                                        class="lazyload"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        @if($review->user->avatar_original !=null)
                                                            data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ uploaded_asset('assets/img/placeholder.jpg') }}"
                                                        @endif
                                                    >
                                                </span>
                                                <div class="media-body">
                                                    <div class="d-flex justify-content-between">
                                                        <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}</h3>
                                                        <span class="rating rating-sm">
                                                            @for ($i=0; $i < $review->rating; $i++)
                                                                <i class="las la-star active"></i>
                                                            @endfor
                                                            @for ($i=0; $i < 5-$review->rating; $i++)
                                                                <i class="las la-star"></i>
                                                            @endfor
                                                        </span>
                                                    </div>
                                                    <div class="opacity-60 mb-2">{{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                                    <p class="comment-text">
                                                        {{ $review->comment }}
                                                    </p>
                                                </div>
                                            </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    @if(count($detailedProduct->reviews) <= 0)
                                        <div class="text-center fs-18 opacity-70">
                                            {{  translate('There have been no reviews for this product yet.') }}
                                        </div>
                                    @endif

                                    @if(Auth::check())
                                        @php
                                            $commentable = false;
                                        @endphp
                                        @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                                            @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                @php
                                                    $commentable = true;
                                                @endphp
                                            @endif
                                        @endforeach
                                        @if ($commentable)
                                            <div class="pt-4">
                                                <div class="border-bottom mb-4">
                                                    <h3 class="fs-17 fw-600">
                                                        {{ translate('Write a review')}}
                                                    </h3>
                                                </div>
                                                <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ translate('Your name')}}</label>
                                                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" disabled required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ translate('Email')}}</label>
                                                                <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" required disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ translate('Rating')}}</label>
                                                        <div class="rating rating-input">
                                                            <label>
                                                                <input type="radio" name="rating" value="1">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="2">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="3">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="4">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="5">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ translate('Comment')}}</label>
                                                        <textarea class="form-control" rows="4" name="comment" placeholder="{{ translate('Your review')}}" required></textarea>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-primary mt-3">
                                                            {{ translate('Submit review')}}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm">
                        <div class="border-bottom p-3">
                            <h3 class="fs-16 fw-600 mb-0 text-left">
                                <span class="">{{ translate('Related Services')}}</span>
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="aiz-carousel gutters-5 half-outside-arrow" data-items="5" data-xl-items="6" data-lg-items="5"  data-md-items="4" data-sm-items="3" data-xs-items="2" data-arrows='true' data-infinite='true'>
                                @foreach (filter_products(\App\Models\Product::where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id)->where('digital', 1))->limit(10)->get() as $key => $related_product)
                                <div class="carousel-box">
                                    <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                        <div class="">
                                            <a href="{{ route('astrology', $related_product->slug) }}" class="d-block">
                                                <img
                                                    class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                                    alt="{{ $related_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="p-md-3 p-2 text-left">
                                            <div class="fs-15">
                                                @if(home_base_price($related_product) != home_discounted_base_price($related_product))
                                                    <del class="fw-600 opacity-50 mr-1">{{ home_base_price($related_product) }}</del>
                                                @endif
                                                <span class="fw-700 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                            </div>
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($related_product->rating) }}
                                            </div>
                                            <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0">
                                                <a href="{{ route('astrology', $related_product->slug) }}" class="d-block text-reset">{{ $related_product->getTranslation('name') }}</a>
                                            </h3>
                                            @if (addon_is_activated('club_point'))
                                                <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                    {{ translate('Club Point') }}:
                                                    <span class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 heading-5">{{ translate('Any question about this product?')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->getTranslation('name') }}" placeholder="{{ translate('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{  translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ translate('Password')}}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                        </div>
                        @if(get_setting('google_login') == 1 ||
                            get_setting('facebook_login') == 1 ||
                            get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if(get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!----- Pranay Rudra -------->
    <div class="modal fade" id="book_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal modal-lg" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 heading-5">Astrology - {{ translate($detailedProduct->getTranslation('name')) }} Report (on-call)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" onsubmit="return bookNow();" id="book-now-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="row mb-3">
                            <div class="col-md-4 col-12">
                                <label for="language_consultation">Select language for consultation*</label>
                                <select name="language_consultation" id="language_consultation" required class="form-control">
                                    <option value=""> Select Language </option>
                                    @if(isset($langs) && !empty($langs) && ($langs != ''))
                                        @foreach($langs as $lang)
                                        <option value="{{ $lang->name }}">{{ $lang->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4 col-12">
                                <label for="preferred_date">Preferred Date*</label>
                                <input class="form-control w-100 input aiz-date-range" data-single="true" data-past-disable="true" name="preferred_date" id="preferred_date" required placeholder="Select preferred date" tabindex="0" type="text" >
                            </div>

                            <div class="col-md-4 col-12">
                                <label for="preferred_time">Preferred Time*</label>
                                <input type="text" name="preferred_time" id="preferred_time" required placeholder="Select preferred time" class="form-control w-100 aiz-time-picker" >
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4 col-12">
                                <label for="place_birth">Place of Birth*</label>
                                <input type="text" name="place_birth" id="place_birth" required class="form-control" placeholder="Enter place of birth">
                            </div>

                            <div class="col-md-4 col-12">
                                <label for="dob">Date of Birth*</label>
                                <input class="form-control w-100 aiz-date-range" data-single="true" data-future-disable="true" name="dob" id="dob" required placeholder="Select date of birth" tabindex="0" type="text" >
                            </div>

                            <div class="col-md-4 col-12">
                                <label for="time_birth">Time of Birth*</label>
                                <input type="text" name="time_birth" id="time_birth" required placeholder="Select time of birth" class="form-control w-100 aiz-time-picker" >
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-12">
                                <label for="male">Select Gender*</label><br>
                                <input type="radio" name="gender" value="male" id="male" class="cursor-pointer" checked="">
                                <label for="male" class="radio-button-label"> Male </label>
                                <input type="radio" name="gender" value="female" id="female" class="cursor-pointer">
                                <label for="female" class="radio-button-label"> Female </label>
                            </div>
                            <div class="col-md-4"></div>
                            <div class="col-md-4"></div>
                        </div>

                        <div class="form-group">
                            <label for="message">Have any questions?*</label>
                            <textarea class="form-control" rows="8" name="message" id="message" placeholder="{{ translate('Please write your queries here...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Close')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Add to cart')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!----- End -------->
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
    		$('#share').share({
    			showLabel: false,
                showCount: false,
                shares: ["email", "twitter", "facebook", "linkedin", "pinterest", "stumbleupon", "whatsapp"]
    		});
    	});

        function CopyToClipboard(containerid) {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(document.getElementById(containerid));
                range.select().createTextRange();
                document.execCommand("Copy");

            } else if (window.getSelection) {
                var range = document.createRange();
                document.getElementById(containerid).style.display = "block";
                range.selectNode(document.getElementById(containerid));
                window.getSelection().addRange(range);
                document.execCommand("Copy");
                document.getElementById(containerid).style.display = "none";

            }
            showFrontendAlert('success', 'Copied');
        }

        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function show_book_modal(){
            @if (Auth::check())
                $('#book_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function bookNow(){
            @if(Auth::check() && Auth::user()->user_type != 'customer')
                AIZ.plugins.notify('warning', "{{ translate('Please Login as a customer to add products to the Cart.') }}");
                return false;
            @endif
            
            $('#addToCart-modal-body').html(null);
            $('#addToCart').modal();
            $('.c-preloader').show();
            var formData = $('#book-now-form').serializeArray();
            formData.push({name: 'quantity', value: '1'});
            $.ajax({
                type:"POST",
                url: '{{ route('cart.addToCart') }}',
                data: formData,
                success: function(data){
                    if(data.status == 1){
                        $('#addToCart-modal-body').html(data.modal_view);
                        updateNavCart(data.nav_cart_view,data.cart_count);
                        window.location.replace("{{ route('cart') }}");
                    }
                    else{
                        $('#addToCart-modal-body').html(null);
                        $('.c-preloader').hide();
                        $('#modal-size').removeClass('modal-lg');
                        $('#addToCart-modal-body').html(data.modal_view);
                    }
                }
            });
            return false;
        }

    </script>
@endsection
