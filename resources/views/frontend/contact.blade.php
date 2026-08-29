@extends('frontend.layouts.app')

@section('meta_title'){{ $page->meta_title }}@stop

@section('meta_description'){{ $page->meta_description }}@stop

@section('meta_keywords'){{ $page->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $page->meta_title }}">
    <meta itemprop="description" content="{{ $page->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($page->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="website">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $page->meta_title }}">
    <meta name="twitter:description" content="{{ $page->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($page->meta_img) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $page->meta_title }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ URL($page->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($page->meta_img) }}" />
    <meta property="og:description" content="{{ $page->meta_description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page->getTranslation('title') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('custom-pages.show_custom_page', $page->slug ) }}">"{{ $page->title }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
	<div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
		    @php echo $page->getTranslation('content'); @endphp
            <div class="row mt-5 mb-5">
                <div class="col-lg-6">
                    <div class="row mt-5">
                      <div class="col-md-6">
                        <div class="info-box">
                          <i class="la la-envelope la-5x text-primary"></i>
                          <h3>{{ translate('Email') }}</h3>
                          <p>{{ get_setting('contact_email') }}<br>&nbsp;</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-box">
                          <i class="la la-phone la-5x text-primary"></i>
                          <h3>{{ translate('Call Us') }}</h3>
                          <p>+91 {{ get_setting('contact_phone') }}<br><br></p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-box">
                          <i class="la la-map-marker la-5x text-primary"></i>
                          <h3>{{ translate('Address') }}</h3>
                          <p>{{ get_setting('contact_address',null,App::getLocale()) }}</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-box">
                          <i class="la la-clock la-5x text-primary"></i>
                          <h3>{{ translate('Open Hours') }}</h3>
                          <p>Monday - Sunday<br>06:00AM - 10:30PM</p>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="text-center mb-1">Leave us your info</h2>
                    <p class="text-center mb-4">and we will get back to you.</p>
                    <form action="#" method="post" id="sdhform" data-action="{{ route('contact.submit') }}" class="">
                      <div class="row gy-4">
                        <div class="form-group col-md-6">
                          <input type="text" name="name" class="form-control" id="name" placeholder="Your Name">
                          <small class="form-control-error invalid-feedback name" role="alert"></small>
                        </div>
                        <div class="form-group col-md-6">
                          <input type="text" name="phone" pattern="[6789][0-9]{9}" id="phone" onkeyup="this.value=this.value.replace(/[^\d]/,'')" class="form-control" placeholder="Your Phone No.">
                          <small class="form-control-error invalid-feedback phone" role="alert"></small>
                        </div>
                        <div class="form-group col-md-12 ">
                          <input type="email" class="form-control" name="email" id="email" placeholder="Your Email">
                          <small class="form-control-error invalid-feedback email" role="alert"></small>
                        </div>
                        @csrf
                        <div class="form-group col-md-12">
                          <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject">
                          <small class="form-control-error invalid-feedback subject" role="alert"></small>
                        </div>
                        <div class="form-group col-md-12">
                          <textarea class="form-control" name="message" rows="6" id="message" placeholder="Message"></textarea>
                          <small class="form-control-error invalid-feedback message" role="alert"></small>
                        </div>
                        <div class="col-md-12 text-center">
                          <button id="sdh-submit" class="btn btn-primary float-md-right" type="submit">Send Message</button>
                        </div>
                      </div>
                    </form>
                </div>
            </div>
        </div>
	</div>
</section>
@endsection
@section('script')
    <script type="text/javascript">
        $(document).on('submit','#sdhform',function(e){
            e.preventDefault();
            var thisId = $(this);
            var submit = thisId.find("#sdh-submit");
            var submitText = submit.text();
            submit.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            var action = $(this).attr('data-action');
            var formData = new FormData(thisId[0]);
            thisId.find('.invalid-feedback').html('');
            thisId.find('.form-control').removeClass('is-invalid');
            $.ajax({
                type:'POST',
                url: action,  
                dataType : 'JSON',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                  submit.attr('disabled','disabled');
                  submit.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                },
                success: function(res){
                    if($.isEmptyObject(res.errors)){
                      if(res.success)
                      {
                        thisId[0].reset();
                        AIZ.plugins.notify('success', res.message);
                      }
                      else
                        AIZ.plugins.notify('warning', res.message);
                    }else{
                        var errorCount = 1;
                        $.each(res.errors, function(i, error) {
                          if(i != '')
                          {
                            thisId.find('.' + i).html('<strong>' + error + '</strong>');
                            thisId.find('#' + i).addClass('is-invalid');
                            if(errorCount === 1)
                              thisId.find('#' + i).focus();
                            errorCount++;
                          }
                        });
                    }
                    submit.removeAttr('disabled');
                    submit.text(submitText);
                },
                error: function(error,s) {
                    console.log(error.responseText);
                    submit.removeAttr('disabled');
                    submit.text(submitText);        
                }
            });
        });
    </script>
@endsection
