<form id="sub_info_form" method="post" enctype="multipart/form-data">
  @csrf
  <div class="card-body py-4 px-sm-3 px-md-5" id="wizard-controller">
      <div class="row">
	       
		  <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
			<label class="form-label" for="subscription">{{ __('index.subscription') }} <span class="text-danger">*</span></label>
			<select class="form-control select2 custom-select subscription" id="subscription" name="subscription" style="width:100%">
			
			</select>
		  </div>  
	  </div>
  </div>
  <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
    <div class="row g-2">
      <div class="col-6 text-start">
        <div class="mb-0">
          <button type="button" id="sub_info_prev" class="btn btn-sm btn-outline-secondary">{{ __('index.previous') }}</button>
        </div>
      </div>
      <div class="col-6 text-end">
        <div class="mb-0">
          <button type="submit" id="sub_info" class="btn btn-sm btn-primary">{{ __('index.next') }}</button>
        </div>
      </div>
    </div>
  </div>  
</form>