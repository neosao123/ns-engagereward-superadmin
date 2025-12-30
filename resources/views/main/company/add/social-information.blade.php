<form id="social_form_add" method="post" enctype="multipart/form-data">
 @csrf
	 <div class="card-body py-4 px-sm-3 px-md-5">
		  <div class="row">
			@foreach($socialApps as $app)
			  <div class="mb-3 col-md-4">
				<div class="d-flex align-items-center gap-2 mb-2">
				  {{-- Logo --}}
				  @if(!empty($app->app_logo))
					<img src="{{ url('storage-bucket?path=' . $app->app_logo) }}"
						 alt="{{ $app->app_name }} Logo"
						 class="img-fluid border rounded"
						 style="width: 40px; height: 40px; object-fit: contain;">
				  @else
					<img src="{{ asset('no-logo.png') }}"
						 alt="No Logo"
						 class="img-fluid border rounded"
						 style="width: 40px; height: 40px; object-fit: contain;">
				  @endif

				  {{-- Checkbox + Label --}}
				  <div style="padding-top:18px;">
					<div class="form-check">
					  <input class="form-check-input" type="checkbox"
							 name="social_apps[{{ $app->id }}][enabled]"
							 id="social_app_{{ $app->id }}" value="1">
					  <label class="form-check-label fw-semibold" for="social_app_{{ $app->id }}">
						{{ $app->app_name }}
					  </label>
					</div>
				  </div>
				</div>
			  </div>
			@endforeach
		  </div>
		</div>
	  <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
		<div class="row g-2">
		  <div class="col-6 text-start">
			<div class="mb-0">
			  <button type="button" id="social_form_add_prev" class="btn btn-sm btn-outline-secondary">{{ __('index.previous') }}</button>
			</div>
		  </div>
		  <div class="col-6 text-end">
			<div class="mb-0">
			  <button type="button" id="social_form_add_next" class="btn btn-sm btn-primary">{{ __('index.next') }}</button>
			</div>
		  </div>
		</div>
	 </div>
</form>
