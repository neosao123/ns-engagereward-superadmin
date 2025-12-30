@extends('layout.default.master')
@php
  $pageTitle = __('index.view_company');
@endphp

@push('styles')
 <link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
 <link rel="stylesheet" href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}">
 <link href="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
 <link href="{{ asset('vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
 <style>
  .error { color: red; }
  #remove_image {
    position: absolute; top: 5px; right: 5px;
    border: none; background: none;
    color: white; font-size: 20px;
    cursor: pointer; z-index: 10;
  }
  #image_preview { width: 125px; position: relative; }
  .iti__country-list {
    white-space: break-spaces !important;
  }
  .iti {
    width: 100% !important;
  }
  .backend-error {
        width: 100%;
        margin-top: 0.25rem;
        font-size: 75%;
        color: #e63757;
    }
 </style>
@endpush

@section('content')
<div class="d-flex mb-4 mt-1">
  <span class="fa-stack me-2 ms-n1">
    <i class="fas fa-circle fa-stack-2x text-300"></i>
    <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
  </span>
  <div class="col">
    <h5 class="mb-0 text-primary position-relative">
      <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view') }}</span>
      <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
    </h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
       @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
       @endif  
       @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))	   
       <li class="breadcrumb-item"><a href="{{ url('/company') }}" class="text-decoration-none text-dark">{{__('index.company')}}</a></li>
       @endif
       <li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
      </ol>
    </nav>
  </div>
   @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))
  <div class="col-auto ms-2">
    <a class="btn btn-falcon-default btn-sm" href="{{ url('company') }}">
      <span class="px-2">{{ __('index.back') }}</span>
    </a>
  </div>
  @endif
</div>

<div class="row">
  <!-- Left card: Company Details (col-md-8) -->
  <div class="col-md-8">
    <div class="card mb-3">
	  @php
	    
		  $latestPurchase = $company->subscriptionPurchases()->latest()->first();
		  $status = $latestPurchase ? $latestPurchase->status : '';
		  $subscription_id=$latestPurchase ? $latestPurchase->id : '';
	  @endphp
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ucfirst($company->company_name)}}</h5>
        @if($company->company_code!="" && $company->setup_status==2)
        <div class="mb-3 d-flex flex-wrap gap-2">
          @if(isset($latestPurchase))
		  <button type="button" class="btn btn-primary btn-sm flex-grow-1" data-val="{{ $subscription_id}}"id="extend_plan_btn">
            <i class="fas fa-calendar-plus me-1"></i> {{ __('index.extend_plan') }}
          </button>
		  @endif
		  
          <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" id="change_plan_btn">
            <i class="fas fa-exchange-alt me-1"></i> {{ __('index.change_plan') }}
          </button>
		  
		   @if($status!="" && ($status=="expired"))
		  <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" data-id="active" id="activate_plan_btn">
            <i class="fas fa-power-off me-1"></i> {{ __('index.activate_plan') }}
          </button>	
		  @endif
		  
        </div>
        @endif
      </div>
      <div class="card-body">
        <div class="row g-3">
          @php $readonly = 'readonly disabled'; @endphp

          <h5 class="text-primary border-bottom pb-2 mb-3">{{ __('index.account_activity') ?? 'Basic Information' }}</h5>
          <div class="col-md-4">
            <label class="form-label">{{ __('index.account_status') }}</label>
            <input class="form-control" type="text" value="{{ $company->account_status }}" {{ $readonly }}>
          </div>

          @if($company->account_status === 'suspended')
          <div class="col-md-12">
            <label class="form-label">{{ __('index.reason') }}</label>
            <textarea class="form-control" {{ $readonly }}>{{ $company->reason ?? 'No reason provided' }}</textarea>
          </div>
          @endif

          <h5 class="text-primary border-bottom pb-2 mb-3">{{ __('index.basic_information') ?? 'Basic Information' }}</h5>

          <input type="hidden" name="company_id" value="{{ $company->id }}">
          <input type="hidden" name="existing_company_logo" value="{{ $company->company_logo }}">

          <div class="col-md-4">
            <label class="form-label">{{ __('index.company_name') }}</label>
            <input class="form-control" type="text" value="{{ $company->company_name }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.trade_name') }}</label>
            <input class="form-control" type="text" value="{{ $company->trade_name }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.legal_type') }}</label>
            <input class="form-control" type="text" value="{{ $company->legal_type }}" {{ $readonly }}>
          </div>

          <div class="col-lg-4 mb-1">
            <label class="form-label" for="company_country_code">{{ __('index.company_country_code') }}</label>
            <select class="form-control select2 custom-select country_code" id="company_country_code" name="company_country_code" style="width:100%" disabled>
              <option value="{{$company->company_country_code}}">{{ $company->officeCountry->country_name ?? '' }}</option>
            </select>
          </div>

          <div class="col-md-12">
            <label class="form-label">{{ __('index.description') }}</label>
            <textarea name="description" class="form-control" rows="4" disabled>{{ $company->description }}</textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.email') }}</label>
            <input class="form-control" type="text" value="{{ $company->email }}" {{ $readonly }}>
          </div>

          @php
            use libphonenumber\PhoneNumberUtil;
            use libphonenumber\NumberParseException;
            $phoneUtil = PhoneNumberUtil::getInstance();
            $number = '';
            if (!empty($company->phone)) {
                try {
                    $parsed = $phoneUtil->parse($company->phone, $company->phone_country);
                    $number = $phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::NATIONAL);
                } catch (NumberParseException $e) {
                    $number = $company->phone;
                }
            }
          @endphp

          <div class="col-md-4">
            <label class="form-label">{{ __('index.phone') }}</label>
            <input type="hidden" name="phone_country" id="phone_country" value="{{ $company->phone_country }}">
            <input class="form-control" type="text" id="phone" name="phone" value="{{ $number }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.website') }}</label>
            <input class="form-control" type="text" value="{{ $company->website }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.reg_number') }}</label>
            <input class="form-control" type="text" value="{{ $company->reg_number }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.gst_number') }}</label>
            <input class="form-control" type="text" value="{{ $company->gst_number }}" {{ $readonly }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('index.account_status') }}</label>
            <input class="form-control" type="text" value="{{ ucfirst($company->account_status) }}" {{ $readonly }}>
          </div>

          <div class="col-md-6 position-relative">
            <label class="form-label">{{ __('index.company_logo') }}</label>
            <div class="mt-2">
              @if ($company->company_logo)
                <img src="{{ url('storage-bucket?path=' . $company->company_logo) }}" alt="Company Logo" class="img-thumbnail" style="max-width: 125px; height: 125px;">
              @else
                <p class="text-muted">No logo uploaded.</p>
              @endif
            </div>
          </div>

          <div class="col-md-12 mt-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="is_active" {{ $company->is_active ? 'checked' : '' }} disabled>
              <label class="form-check-label" for="is_active">{{ __('index.active') }}</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="is_verified" {{ $company->is_verified ? 'checked' : '' }} disabled>
              <label class="form-check-label" for="is_verified">{{ __('index.verified') }}</label>
            </div>
          </div>
        </div>

        <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">{{ __('index.office_address') ?? 'Office Address' }}</h5>
        <div class="row">
          <div class="mb-1 col-lg-6">
            <label class="form-label" for="office_address_line_one">{{ __('index.office_address_line_one') }}</label>
            <textarea class="form-control" name="office_address_line_one" id="office_address_line_one" rows="2" disabled>{{ $company->office_address_line_one ?? '' }}</textarea>
          </div>

          <div class="mb-1 col-lg-6">
            <label class="form-label" for="office_address_line_two">{{ __('index.office_address_line_two') }}</label>
            <textarea class="form-control" name="office_address_line_two" id="office_address_line_two" rows="2" disabled>{{ $company->office_address_line_two ?? '' }}</textarea>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="office_address_city">{{ __('index.office_address_city') }}</label>
            <input class="form-control" type="text" name="office_address_city" id="office_address_city" value="{{ $company->office_address_city ?? '' }}" disabled>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="office_address_province_state">{{ __('index.office_address_province_state') }}</label>
            <input class="form-control" type="text" name="office_address_province_state" id="office_address_province_state" value="{{ $company->office_address_province_state ?? '' }}" disabled>
          </div>

          <div class="col-lg-4 mb-1">
            <label class="form-label" for="office_address_country_code">{{ __('index.office_address_country_code') }}</label>
            <select class="form-control select2 custom-select country_code" id="office_address_country_code" name="office_address_country_code" style="width:100%" disabled>
              <option value="{{$company->office_address_postal_code}}">{{ $company->officeCountry->country_name ?? '' }}</option>
            </select>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="office_address_postal_code">{{ __('index.office_address_postal_code') }}</label>
            <input class="form-control" type="text" name="office_address_postal_code" id="office_address_postal_code" value="{{ $company->office_address_postal_code ?? '' }}" disabled>
          </div>
        </div>

        <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">{{ __('index.billing_address') ?? 'Billing Address' }}</h5>
        <div class="row">
          <div class="mb-1 col-lg-6">
            <label class="form-label" for="billing_address_line_one">{{ __('index.billing_address_line_one') }}</label>
            <textarea class="form-control" name="billing_address_line_one" id="billing_address_line_one" rows="2" disabled>{{ $company->billing_address_line_one ?? '' }}</textarea>
          </div>

          <div class="mb-1 col-lg-6">
            <label class="form-label" for="billing_address_line_two">{{ __('index.billing_address_line_two') }}</label>
            <textarea class="form-control" name="billing_address_line_two" id="billing_address_line_two" rows="2" disabled>{{ $company->billing_address_line_two ?? '' }}</textarea>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="billing_address_city">{{ __('index.billing_address_city') }}</label>
            <input class="form-control" type="text" name="billing_address_city" id="billing_address_city" value="{{ $company->billing_address_city ?? '' }}" disabled>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="billing_address_province_state">{{ __('index.billing_address_province_state') }}</label>
            <input class="form-control" type="text" name="billing_address_province_state" id="billing_address_province_state" value="{{ $company->billing_address_province_state ?? '' }}" disabled>
          </div>

          <div class="col-lg-4 mb-1">
            <label class="form-label" for="billing_address_country_code">{{ __('index.billing_address_country_code') }}</label>
            <select class="form-control select2 custom-select country_code" id="billing_address_country_code" name="billing_address_country_code" style="width:100%" disabled>
              <option value="{{$company->billing_address_postal_code}}">{{ $company->billingCountry->country_name ?? '' }}</option>
            </select>
          </div>

          <div class="mb-1 col-lg-4">
            <label class="form-label" for="billing_address_postal_code">{{ __('index.billing_address_postal_code') }}</label>
            <input class="form-control" type="text" name="billing_address_postal_code" id="billing_address_postal_code" value="{{ $company->billing_address_postal_code ?? '' }}" disabled>
          </div>
        </div>

        <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">{{ __('index.social_media_app')}}</h5>
        <div class="row">
          @foreach($socialApps as $app)
            @php
              $setting = $socialMediaSettings->firstWhere('social_media_app_id', $app->id);
            @endphp

            @if($setting)
              @php
                $appLink = $setting->social_media_page_link ?? '';
              @endphp

              <div class="mb-3 col-md-4">
                <div class="d-flex align-items-start gap-2 mb-2">
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

                  <div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox"
                             name="social_apps[{{ $app->id }}][enabled]"
                             id="social_app_{{ $app->id }}"
                             value="1"
                             checked
                             onclick="return false;"
                             readonly>
                      <label class="form-check-label fw-semibold" for="social_app_{{ $app->id }}">
                        {{ $app->app_name }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            @endif
          @endforeach
        </div>

        <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">{{ __('index.documents') ?? 'Documents' }}</h5>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="bg-light">
                  <tr>
                    <th>{{ __('index.document_type') }}</th>
                    <th>{{ __('index.document_number') }}</th>
                    <th>{{ __('index.document_file') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($company->companyDocument) > 0)
                    @foreach($company->companyDocument as $document)
                      <tr>
                        <td>{{ $document->document_type }}</td>
                        <td>{{ $document->document_number }}</td>
                        <td>
                          @if ($document->document_file)
                            <a href="{{ url('storage-bucket?path=' . $document->document_file) }}" target="_blank" class="btn btn-link">
                              {{ __('index.view') }}
                            </a>
                          @else
                            <span class="text-muted">N/A</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="3" class="text-center text-muted">No documents available.</td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div> <!-- card-body -->
    </div> <!-- card -->
  </div> <!-- col-md-8 -->

  <!-- Right card: Current Plan (col-md-4) -->
  @if(isset($company->subscriptionPurchases))
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.current_plan') }}</h5>
      </div>
	  @php $purchase = $company->subscriptionPurchases()->latest()->first(); @endphp
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-12 mb-2">
            <input class="form-control" type="hidden" name="company_code" value="{{$company->company_code??""}}" readonly>
            <label class="form-label">{{ __('index.subscription_title') }}</label>
			
			<input class="form-control" type="text" value="{{$purchase->subscription_title??""}}" readonly>
            <input class="form-control" type="hidden" id="subscription_id" value="{{$purchase->id??""}}" readonly>
          </div>

          <div class="col-md-12 mb-2">
            <label class="form-label">{{ __('index.months') }}</label>
            <input class="form-control" type="text" value="{{$purchase->subscription_months??""}}" readonly>
          </div>

          <div class="col-md-12 mb-2">
            <label class="form-label">{{ __('index.per_month_price') }}</label>
            <input class="form-control" type="text" value="{{ $purchase->subscription_per_month_price??"" }}" readonly>
          </div>
		  
	
		
		<!-- Discount Type -->
		<div class="col-md-12 mb-2">
			<label class="form-label">{{ __('index.discount_type') }}</label>
			  <input class="form-control" type="text" value="{{ $purchase->discount_type??"" }}" readonly>
		</div>


          <div class="col-md-12 mb-2">
            <label class="form-label">{{ __('index.discount_value') }}</label>
            <input class="form-control" type="text" value="{{ $purchase->discount_value??"" }}" readonly>
          </div>
		  
		  
		   <div class="col-md-12 mb-2">
				<label class="form-label">{{ __('index.total_price') }} </label>
				<input class="form-control " type="text"  value="{{ $purchase->subscription_total_price??"" }}" readonly>
		   </div>
			  

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __('index.from_date') }}</label>
            <input class="form-control" type="text" value="{{ \Carbon\Carbon::parse($purchase->from_date)->format('d-m-Y')??"" }}" readonly>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __('index.to_date') }}</label>
            <input class="form-control" type="text" value="{{ \Carbon\Carbon::parse($purchase->to_date)->format('d-m-Y')??"" }}" readonly>
          </div>
          @if(isset($purchase->status))
          <div class="col-md-12">
            <label class="form-label">{{ __('index.status') }}</label>
            <select class="form-select" disabled>
              <option value="active" {{ $purchase->status == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ $purchase->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
			  
            </select>
          </div>
		  @endif
        </div>
      </div>

    </div>
  </div> <!-- col-md-4 -->
  @endif
</div> <!-- row -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form id="form-extend-subscription" method="POST" enctype="multipart/form-data">
	     @csrf
        @method('PUT')
	  <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabel">{{__('index.current_plan')}} </h5>
      
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
	    
         <div class="row">			
			<input class="form-control" type="hidden"  name="company_code" value="{{$company->company_code??""}}" readonly>
            <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
			    
				 <label class="form-label">{{ __('index.subscription_title') }}</label>
			     <input class="form-control" type="text" value="{{$purchase->subscription_title??""}}" readonly>
			</div>
			 <div class="col-md-6 mb-2">
				<label class="form-label">{{ __('index.months') }}</label>
				<input class="form-control" type="text" id="subscription_months" value="{{$purchase->subscription_months??""}}" readonly>
			  </div>

			  <div class="col-md-6 mb-2">
				<label class="form-label">{{ __('index.per_month_price') }}</label>
				<input class="form-control" type="text" id="subscription_per_month_price" value="{{ $purchase->subscription_per_month_price??"" }}" readonly>
			  </div>
			  
			   <div class="col-md-4 mb-2">
			       <label class="form-label">{{ __('index.discount_type') }}</label>
			       <input class="form-control" type="text" value="{{ $purchase->discount_type??"" }}" readonly>
			   </div>
               <div class="col-md-4 mb-2">
			        <label class="form-label">{{ __('index.discount_value') }}</label>
                    <input class="form-control" type="text" value="{{ $purchase->discount_value??"" }}" readonly>
			   </div>			   
			  <div class="col-md-4 mb-2">
				<label class="form-label">{{ __('index.total_price') }} </label>
				<input class="form-control " type="text" id="subscription_total_price" value="{{ $purchase->subscription_total_price??"" }}" readonly>
			  </div>
			  
			  <div class="col-md-6 mb-2">
				<label class="form-label">{{ __('index.from_date') }} <span class="text-danger">*</span></label>
				<input class="form-control flatpickr" type="text" name="from_date" id="from_date"
					value="{{ !empty($purchase->from_date) ? \Carbon\Carbon::parse($purchase->from_date)->format('d-m-Y') : '' }}">
				@error('from_date') <span class="backend-error">{{ $message }}</span> @enderror
			</div>

			<div class="col-md-6 mb-2">
				<label class="form-label">{{ __('index.to_date') }} <span class="text-danger">*</span></label>
				<input class="form-control flatpickr" type="text" name="to_date" id="to_date"
					value="{{ !empty($purchase->to_date) ? \Carbon\Carbon::parse($purchase->to_date)->format('d-m-Y') : '' }}">
				@error('to_date') <span class="backend-error">{{ $message }}</span> @enderror
			</div>
			@if(isset($purchase->status))
			 <div class="col-md-6 mb-2 d-none">
				<label class="form-label">{{ __('index.status') }} <span class="text-danger">*</span></label>
				 <select class="form-select" name="status">
				  <option value="">Select Status</option>
				  <option value="active" {{ $purchase->status == 'active' ? 'selected' : '' }}>Active</option>
				  <option value="inactive" {{ $purchase->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
				 
				</select>
			 </div>
			 @endif
		 </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="button" class="btn btn-primary" id="expand_plan">Submit</button> 
      </div>
	 </form>
    </div>
  </div>
</div>


<div class="modal fade" id="subscriptionModal" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form id="form-subscription-add" method="POST" enctype="multipart/form-data">
         @csrf
    
        <div class="modal-header">
          <h5 class="modal-title" id="subscriptionModalLabel">{{__('index.new_plan')}} </h5>
        
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input class="form-control" type="hidden" name="company_code" value="{{$company->company_code??""}}" readonly>
           <div class="row">      
            <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
				
                <label>{{ __('index.subscription_title') }} <span class="text-danger">*</span></label>
				 <input class="form-control" type="hidden" value="{{$company->id??""}}" name="company_id" readonly>
				<select class="form-control select2 custom-select subscription" name="subscription" style="width:100%">
			
			    </select>
				@error('subscription')
					<span class="text-danger">{{ $message }}</span>
				@enderror
            </div>
         </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="add_subscription_btn">Submit</button> 
        </div>
       </form>
    </div>
  </div>
</div>
@endsection


@push('scripts')
<!-- Then load the JS files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

<script>
  // Wait for the DOM to be fully loaded
	document.addEventListener('DOMContentLoaded', function() {
		const phoneInput = document.getElementById("phone");
		const countryInput = document.getElementById("phone_country");
		
		// Initialize with existing values
		const initialCountry = countryInput.value.toLowerCase() || "auto";
		const initialNumber = phoneInput.value || "";
		
		const iti = window.intlTelInput(phoneInput, {
			utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
			preferredCountries: ['us', 'gb', 'ca', 'au', 'in'],
			separateDialCode: true,
			initialCountry: initialCountry,
			nationalMode: false, // Show full international number
			autoPlaceholder: "off", // Disable automatic placeholder
		});

		// Set the initial number properly
		if (initialNumber) {
			// Format the existing number with country code
			const countryData = iti.getSelectedCountryData();
			const fullNumber = '+' + countryData.dialCode + initialNumber;
			iti.setNumber(fullNumber);
		}

		// Update hidden country field when country changes
		phoneInput.addEventListener('countrychange', function() {
			countryInput.value = iti.getSelectedCountryData().iso2;
		});
	});
</script>
<script>
 var id=$("#subscription_id").val();
    var baseUrl = "{{ url('/') }}";
	   var csrfToken = "{{ csrf_token() }}";
</script>
   <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
   <script src="{{ asset('vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
 <script src="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="{{ asset('init/company/view.js?v=' . time()) }}"></script>
  

@endpush
