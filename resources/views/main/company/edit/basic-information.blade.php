<form id="basic_info_form" method="post" enctype="multipart/form-data">
    @csrf
    <div class="card-body" id="wizard-controller">
        <div class="row">
            <input class="form-control d-none" type="number" name="company_id" id="company_id" value="{{ $company->id }}">
            <input type="hidden" name="existing_company_logo" value="{{ $company->company_logo }}">

            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="company_name">{{ __('index.company_name') }}<span
                        class="text-danger">*</span></label>
                <div class="">
                    <input class="form-control" type="text" name="company_name" id="company_name"
                        value="{{ $company->company_name }}">
                </div>
            </div>
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="trade_name">{{ __('index.trade_name') }}</label>
                <div class="">
                    <input class="form-control" type="text" name="trade_name" id="trade_name"
                        value="{{ $company->trade_name }}">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
                <label class="form-label" for="company_country_code">{{ __('index.company_country_code') }} <span
                        class="text-danger">*</span></label>
                <select class="form-control select2 custom-select country_code" id="company_country_code"
                    name="company_country_code" style="width:100%">
                    <option value="{{ $company->company_country_code }}">
                        {{ $company->companyCountry->country_name ?? '' }}</option>
                </select>
            </div>
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="legal_type">{{ __('index.legal_type') }}<span
                        class="text-danger">*</span></label>
                <div class="">
                    <select class="form-select custom-select" id="legal_type" name="legal_type" style="width:100%">
                        <option>Select Company Type</option>
                        <option value="sole_proprietorship"
                            {{ $company->legal_type == 'sole_proprietorship' ? 'selected' : '' }}>Sole Proprietorship
                        </option>
                        <option value="partnership" {{ $company->legal_type == 'partnership' ? 'selected' : '' }}>
                            Partnership</option>
                        <option value="limited_liability_partnership"
                            {{ $company->legal_type == 'limited_liability_partnership' ? 'selected' : '' }}>LLP
                            (Limited Liability Partnership)</option>
                        <option value="private_limited"
                            {{ $company->legal_type == 'private_limited' ? 'selected' : '' }}>Private Limited (Pvt Ltd)
                        </option>
                        <option value="public_limited"
                            {{ $company->legal_type == 'public_limited' ? 'selected' : '' }}>Public Limited (Ltd)
                        </option>
                        <option value="limited_liability_company"
                            {{ $company->legal_type == 'limited_liability_company' ? 'selected' : '' }}>LLC (Limited
                            Liability Co.)</option>
                        <option value="one_person_company"
                            {{ $company->legal_type == 'one_person_company' ? 'selected' : '' }}>One Person Company
                            (OPC)</option>
                        <option value="non_profit_organization"
                            {{ $company->legal_type == 'non_profit_organization' ? 'selected' : '' }}>Non-Profit
                            Organization</option>
                    </select>
                </div>
            </div>

            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="email">{{ __('index.email') }}<span
                        class="text-danger">*</span></label>
                <div class="">
                    <input class="form-control" type="text" name="email" id="email"
                        value="{{ $company->email }}">
                </div>
            </div>

            @php
                use libphonenumber\PhoneNumberUtil;
                use libphonenumber\NumberParseException;

                $number = '';
                if ($company->phone) {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $parsed = $phoneUtil->parse($company->phone, $company->phone_country ?? ''); // default to 'IN' if missing
                        $number = $phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::NATIONAL);
                    } catch (NumberParseException $e) {
                        $number = $company->phone; // fallback to original value
                    }
                }
            @endphp
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="phone">{{ __('index.phone') }}</label>
                <div class="">
                    <input type="hidden" name="phone_country" id="phone_country"
                        value="{{ $company->phone_country }}">
                    <input class="form-control" type="tel" name="phone" id="phone"
                        value="{{ $number }}" oninput="this.value = this.value.replace(/\D/g, '')">
                </div>
            </div>
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="website">{{ __('index.website') }}<span class="text-muted">(e.g.,
                        https://www.sample.com)</span></label>
                <div class="">
                    <input class="form-control" type="text" name="website" id="website"
                        value="{{ $company->website }}">
                </div>
            </div>
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="reg_number">{{ __('index.reg_number') }}</label>
                <div class="">
                    <input class="form-control" type="text" name="reg_number" id="reg_number"
                        value="{{ $company->reg_number }}">
                </div>
            </div>
            <div class="mb-1 col-lg-4 col-md-6 col-sm-12">
                <label class=" form-label" for="gst_vat_number">{{ __('index.gst_number') }}</label>
                <div class="">
                    <input class="form-control" type="text" name="gst_number" id="gst_number"
                        value="{{ $company->gst_number }}">
                </div>
            </div>


            <div class="mb-1 col-lg-12 col-md-12 col-sm-12">
                <label class="form-label" for="description">{{ __('index.description') }}</label>
                <div class="">
                    <textarea class="form-control" name="description" id="description" rows="3">{{ $company->description }}</textarea>
                </div>
            </div>

            <!-- Comapny Logo -->
            <div class="mb-1 col-lg-6 col-md-6 col-sm-6 position-relative">
                <label class="mb-0 form-label">{{ __('index.company_logo') }}
                </label>
                <p class="mb-0">
                    <small class="form-label">
                        {{ __('index.accept_format') }}
                        {{ implode(', ', [__('index.jpg'), __('index.jpeg'), __('index.png')]) }}
                    </small>
                </p>
                <p><small class="form-label">{{ __('index.512x512') }}</small></p>

                <input type="file" class="form-control" name="company_logo" id="company_logo"
                    accept=".jpg, .jpeg, .png" />
                <div id="logo_preview" class="mt-2 {{ isset($company->company_logo) ? '' : 'd-none' }}">
                    <img id="preview_img" data-id="{{ $company->id }}"
                        src="{{ isset($company->company_logo) ? url('storage-bucket?path=' . $company->company_logo) : '#' }}"
                        alt="Logo Preview" class="img-fluid" style="max-width: 125px; height: 125px;" />
                    <button type="button" id="remove_image" class="btn btn-danger"
                        style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        &times;
                    </button>
                </div>
                <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
            </div>


            <div class="col-12">
                <div class="mb-1 d-flex gap-4">
                    <!-- is_active checkbox -->
                    <div class="form-check d-inline">
                        <input class="form-check-input" name="is_active" type="checkbox" value="1"
                            id="is_active" {{ $company->is_active == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ __('index.active') }}
                        </label>
                    </div>

                    <!-- is_verified checkbox -->
                    <div class="form-check d-inline">
                        <input class="form-check-input" name="is_verified" type="checkbox" value="1"
                            id="is_verified" {{ $company->is_verified == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_verified">
                            {{ __('index.verified') }}
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
        <div class="row g-2">
            <div class="col-12 text-end">
                <div class="mb-0">
                    <button id="basic_info_update" class="btn btn-sm btn-primary">{{ __('index.next') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>
