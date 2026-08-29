@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('Astrology') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('astrologies.all') }}">"{{ translate('Astrology') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
            <div class="mb-3 bg-white shadow-sm rounded">
                <div class="p-3 p-lg-4">
                    <div class="row gutters-5 row-cols-xxl-6 row-cols-xl-6 row-cols-lg-5 row-cols-md-4 row-cols-3">
                            @foreach ($products as $key => $product)
                                <div class="col">
                                    @include('frontend.partials.astrology_box',['product' => $product])
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>
    </div>
</section>

@endsection
