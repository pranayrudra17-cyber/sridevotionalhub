@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('All Categories') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('categories.all') }}">"{{ translate('All Categories') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
        @foreach ($categories as $key => $category)
            <div class="mb-3 bg-white shadow-sm rounded">
                <div class="text-dark p-3 d-flex align-items-center @if(count($category->childrenCategories) > 0) dropdown-toggle filter-section collapsed @endif " @if(count($category->childrenCategories) > 0) data-toggle="collapse" data-target="#{{ $category->slug }}" style="white-space: normal;" @endif>
                    <div class="size-60px overflow-hidden p-1 border mr-3">
                        <img src="{{ uploaded_asset($category->banner) }}" alt="" class="img-fit h-100">
                    </div>
                    <a href="{{ route('products.category', $category->slug) }}" class="text-reset fs-16 fs-md-20 fw-700 hov-text-primary w-100">{{  $category->getTranslation('name') }}</a>
                </div>
                @if(!empty(\App\Utility\CategoryUtility::get_immediate_children_ids($category->id)))
                <div class="px-4 py-2 collapse" id="{{ $category->slug }}">
                    <div class="row row-cols-xl-5 row-cols-md-3 row-cols-sm-2 row-cols-1">
                        @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id)
                        <div class="col-lg-4 col-6 text-left">
                            <h6 class="mb-3"><a class="text-reset fw-600 fs-14" href="{{ route('products.category', \App\Models\Category::find($first_level_id)->slug) }}">{{ \App\Models\Category::find($first_level_id)->getTranslation('name') }}</a></h6>
                            <ul class="mb-3 list-unstyled pl-2">
                                @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($first_level_id) as $key => $second_level_id)
                                <li class="mb-2">
                                    <a class="text-reset" href="{{ route('products.category', \App\Models\Category::find($second_level_id)->slug) }}" >{{ \App\Models\Category::find($second_level_id)->getTranslation('name') }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</section>

@endsection
