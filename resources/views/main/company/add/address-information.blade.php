<form id="addr_info_form" method="post" enctype="multipart/form-data">
  @csrf
  <div class="card-body py-4 px-sm-3 px-md-5" id="wizard-controller">
    <div class="row">
      {{-- Office Address Fields --}}
      <div class="mb-1 col-lg-6 col-md-6 col-sm-12">
        <label class="form-label" for="office_address_line_one">{{ __('index.office_address_line_one') }} <span class="text-danger">*</span></label>
        <textarea class="form-control" name="office_address_line_one" id="office_address_line_one" rows="2">{{ old('office_address_line_one') }}</textarea>
      </div>

      <div class="mb-1 col-lg-6 col-md-6 col-sm-12">
        <label class="form-label" for="office_address_line_two">{{ __('index.office_address_line_two') }}</label>
        <textarea class="form-control" name="office_address_line_two" id="office_address_line_two" rows="2">{{ old('office_address_line_two') }}</textarea>
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="office_address_city">{{ __('index.office_address_city') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="office_address_city" id="office_address_city" value="{{ old('office_address_city') }}">
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="office_address_province_state">{{ __('index.office_address_province_state') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="office_address_province_state" id="office_address_province_state" value="{{ old('office_address_province_state') }}">
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
        <label class="form-label" for="office_address_country_code">{{ __('index.office_address_country_code') }} <span class="text-danger">*</span></label>
        <select class="form-control select2 custom-select country_code" id="office_address_country_code" name="office_address_country_code" style="width:100%">
        
        </select>
      </div>  

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="office_address_postal_code">{{ __('index.office_address_postal_code') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="office_address_postal_code" id="office_address_postal_code" value="{{ old('office_address_postal_code') }}">
      </div>

      {{-- Billing Address Checkbox --}}
      <div class="mb-3 col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_billing_address_same" id="is_billing_address_same" value="1" {{ old('is_billing_address_same') ? 'checked' : '' }}>
          <label class="form-check-label" for="is_billing_address_same">
            {{ __('index.is_billing_address_same') }}
          </label>
        </div>
      </div>

      {{-- Billing Address Fields --}}
      <div class="mb-1 col-lg-6 col-md-6 col-sm-12">
        <label class="form-label" for="billing_address_line_one">{{ __('index.billing_address_line_one') }} <span class="text-danger">*</span></label>
        <textarea class="form-control" name="billing_address_line_one" id="billing_address_line_one" rows="2">{{ old('billing_address_line_one') }}</textarea>
      </div>

      <div class="mb-1 col-lg-6 col-md-6 col-sm-12">
        <label class="form-label" for="billing_address_line_two">{{ __('index.billing_address_line_two') }}</label>
        <textarea class="form-control" name="billing_address_line_two" id="billing_address_line_two" rows="2">{{ old('billing_address_line_two') }}</textarea>
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="billing_address_city">{{ __('index.billing_address_city') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="billing_address_city" id="billing_address_city" value="{{ old('billing_address_city') }}">
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="billing_address_province_state">{{ __('index.billing_address_province_state') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="billing_address_province_state" id="billing_address_province_state" value="{{ old('billing_address_province_state') }}">
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
        <label class="form-label" for="billing_address_country_code">{{ __('index.billing_address_country_code') }} <span class="text-danger">*</span></label>
        <select class="form-control select2 custom-select country_code" id="billing_address_country_code" name="billing_address_country_code" style="width:100%">
         
        </select>
      </div>

      <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
        <label class="form-label" for="billing_address_postal_code">{{ __('index.billing_address_postal_code') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="billing_address_postal_code" id="billing_address_postal_code" value="{{ old('billing_address_postal_code') }}">
      </div>
    </div>
  </div>

  <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
    <div class="row g-2">
      <div class="col-6 text-start">
        <div class="mb-0">
          <button type="button" id="address_info_prev" class="btn btn-sm btn-outline-secondary">{{ __('index.previous') }}</button>
        </div>
      </div>
      <div class="col-6 text-end">
        <div class="mb-0">
          <button type="submit" id="address_info" class="btn btn-sm btn-primary">{{ __('index.next') }}</button>
        </div>
      </div>
    </div>
  </div>
</form>
