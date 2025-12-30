@extends('layout.default.master', ['pageTitle' => 'Dashboard'])
@push('styles')
@endpush
@section('content')
 <h5 class="">Dashboard</h5>
  <div class="row g-3 mt-2">
	<div class="col-sm-6 col-md-3">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(../img/icons/spot-illustrations/corner-1.png);"></div><!--/.bg-holder-->
        <div class="card-body position-relative">
          <h6>{{ __('index.company') }}<span class="badge badge-subtle-warning rounded-pill ms-2"></span></h6>
          <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif" data-countup='{"endValue":43594,"prefix":"$"}'>{{$company}}</div><a class="fw-semi-bold fs-10 text-nowrap text-primary"
            href="{{url('company')}}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>
	<div class="col-sm-6 col-md-3">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(../img/icons/spot-illustrations/corner-2.png);"></div><!--/.bg-holder-->
        <div class="card-body position-relative">
          <h6>{{ __('index.social_media_app') }}<span class="badge badge-subtle-warning rounded-pill ms-2"></span></h6>
          <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif" data-countup='{"endValue":43594,"prefix":"$"}'>{{$SocialMediaApp}}</div><a class="fw-semi-bold fs-10 text-nowrap text-primary"
            href="{{url('social-media-apps')}}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>
</div>
@endsection
@push('scripts')

@endpush