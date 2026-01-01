<form id="basic_info_form" method="post" enctype="multipart/form-data">
  @csrf
  <div class="card-body" id="wizard-controller">
    <div class="row">
      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="company_name">{{__('index.company_name')}}<span class="text-danger">*</span></label>
        <div class="">
          <input class="form-control" type="text" name="company_name" id="company_name" value="">
        </div>
      </div>
	   <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="trade_name">{{__('index.trade_name')}}</label>
        <div class="">
          <input class="form-control" type="text" name="trade_name" id="trade_name" value="">
        </div>
      </div>
	   <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
        <label class="form-label" for="company_country_code">{{ __('index.company_country_code') }} <span class="text-danger">*</span></label>
        <select class="form-control select2 custom-select country_code" id="company_country_code" name="company_country_code" style="width:100%">

        </select>
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="legal_type">{{__('index.legal_type')}}<span class="text-danger">*</span></label>
        <div class="">
          <select class="form-select custom-select" id="legal_type" name="legal_type" style="width:100%">
			  <option value="" selected disabled>Select Company Type</option>
			  <option value="sole_proprietorship">Sole Proprietorship</option>
			  <option value="partnership">Partnership</option>
			  <option value="limited_liability_partnership">LLP (Limited Liability Partnership)</option>
			  <option value="private_limited">Private Limited (Pvt Ltd)</option>
			  <option value="public_limited">Public Limited (Ltd)</option>
			  <option value="limited_liability_company">LLC (Limited Liability Co.)</option>
			  <option value="one_person_company">One Person Company (OPC)</option>
			  <option value="non_profit_organization">Non-Profit Organization</option>
		  </select>

        </div>
      </div>
	  <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="email">{{__('index.email')}}<span class="text-danger">*</span></label>
        <div class="">
          <input class="form-control" type="text" name="email" id="email" value="">
        </div>
      </div>
      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="phone">{{__('index.phone')}}</label>
        <div class="">
		   <input type="hidden" name="phone_country" id="phone_country">
          <input class="form-control" type="tel" name="phone" id="phone" value="" oninput="this.value = this.value.replace(/\D/g, '')">
        </div>
      </div>
	  <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="website">{{__('index.website')}}<span class="text-muted">(e.g., https://www.sample.com)</span></label>
        <div class="">
          <input class="form-control" type="text" name="website" id="website" value="">
        </div>
      </div>
	  <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="reg_number">{{__('index.reg_number')}}</label>
        <div class="">
          <input class="form-control" type="text" name="reg_number" id="reg_number" value="">
        </div>
      </div>
	  <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class=" form-label" for="gst_vat_number">{{__('index.gst_number')}}</label>
        <div class="">
          <input class="form-control" type="text" name="gst_number" id="gst_number" value="">
        </div>
      </div>


		<div class="mb-1 col-lg-12 col-md-12 col-sm-12">
			<label class="form-label" for="description">{{__('index.description')}}</label>
			<div class="">
				<textarea class="form-control" name="description" id="description" rows="3"></textarea>
			</div>
		</div>
	      <!-- Comapny Logo -->
		<div class="mb-1 col-lg-6 col-md-6 col-sm-6 position-relative">
		  <label class="mb-0 form-label">{{ __('index.company_logo') }} </label>

		  <p><small class="form-label">{{ __('index.512x512') }}</small></p>

		  <input type="file" class="form-control" name="company_logo" id="company_logo" accept=".jpg, .jpeg, .png" />
		  <div id="image_preview" class="mt-2" style="display: none;">
			<img id="preview_img" src="#" alt="Image Preview" class="img-fluid" style="width: 125px; height: 125px;" />
			<button type="button" id="remove_image" class="btn btn-danger"
			  style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
			  &times; <!-- This will display an 'X' -->
			</button>
		  </div>
		  <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
		</div>

    </div>
  </div>
  <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
    <div class="row g-2">
      <div class="col-12 text-end">
        <div class="mb-0">
          <button id="basic_info" class="btn btn-sm btn-primary">{{ __('index.next') }}</button>
        </div>
      </div>
    </div>
  </div>
</form>
