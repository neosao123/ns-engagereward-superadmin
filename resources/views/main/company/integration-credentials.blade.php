@extends('layout.default.master', ['pageTitle' => __('index.integration_credentials')])
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .error {
      color: red;
    }
    .invalid-feedback{
        display:block !important;
    }
    #remove_image {
      position: absolute;
      top: 5px;
      right: 5px;
      border: none;
      background: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      z-index: 10;
    }
    #image_preview {
      width: 125px;
      position: relative;
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
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.social_platform_integration')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(isRolePermission(auth()->user()->role_id, 'Company.List'))
                        <li class="breadcrumb-item"><a href="{{ url('/company') }}" class="text-decoration-none text-dark">{{__('index.company')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.social_platform_integration')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(isRolePermission(auth()->user()->role_id, 'Company.List'))
        <div class="col-auto ms-2 align-items-center">
            <a href="{{ url('company') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
        </div>
    @endif
</div>

<div class="col-lg-12">
    <!-- Loop through each social media platform -->
    @foreach($getSocialDetails as $socialMedia)
    <div class="card mb-3 social-media-card" data-social-id="{{ $socialMedia['id'] }}">
        <form class="social-media-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="company_id" value="{{ request('companyId') }}">
            <input type="hidden" name="social_media_id" value="{{ $socialMedia['id'] }}">

            <div class="card-header bg-light">
                <h5 class="mb-0">{{ $socialMedia['name'] }} {{ __('index.integration_credentials') }}</h5>
            </div>

            <div class="card-body">
                <div class="documents-repeater">
                    @php
                        $socialCredentials = array_filter($credentials, function($cred) use ($socialMedia) {
                            return isset($cred['social_media_id']) && $cred['social_media_id'] == $socialMedia['id'];
                        });
                        
                        $fields = [
                            ['label' => 'App ID', 'type' => 'App ID'],
                            ['label' => 'App Secret', 'type' => 'App Secret'],
                            ['label' => 'Callback URL', 'type' => 'Callback URL'],
                        ];
                    @endphp

                    @foreach($fields as $index => $field)
                        @php
                            $credential = collect($socialCredentials)->firstWhere('type', $field['type']);
                            $val = $credential['value'] ?? '';
                            
                            if ($field['type'] == 'Callback URL' && empty($val)) {
                                $cmCode = strtolower($company->company_unique_code);
                                $platform = strtolower($socialMedia['name']);
                                if (str_contains($platform, 'instagram')) {
                                    $platform = 'instagram';
                                } elseif (str_contains($platform, 'facebook')) {
                                    $platform = 'facebook';
                                } else {
                                    $platform = preg_replace('/[^a-z0-9]/', '', $platform);
                                }
                                $baseUrl = rtrim(env('ADMIN_API_URL'), '/');
                                $val = $baseUrl . '/' . $cmCode . '/social/auth/' . $platform . '/callback';
                            }
                        @endphp
                        <div class="document-row mb-3 border-bottom pb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">{{ $field['label'] }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"
                                           name="integration_credentials[{{ $index }}][type]"
                                           value="{{ $field['type'] }}" readonly>
                                    <input type="hidden"
                                           name="integration_credentials[{{ $index }}][id]"
                                           value="{{ $credential['id'] ?? '#' }}">
                                </div>

                                <div class="col-md-8 mb-2">
                                    <label class="form-label">{{ __('index.value') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control value"
                                           name="integration_credentials[{{ $index }}][value]"
                                           id="value-{{ $credential['id'] ?? 'new-'.$socialMedia['id'].'-'.$index }}"
                                           value="{{ $val }}"
                                           data-original-value="{{ $val }}"
                                           placeholder="Enter {{ $field['label'] }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Action Button for this social media -->
                <div class="row mt-3">
                    <div class="col-12">
                        @if(count($socialCredentials) > 0)
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-show-hidden float-end" data-social-id="{{ $socialMedia['id'] }}">
                            <i class="far fa-eye me-1"></i> Show
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-end">
                <button class="btn btn-primary submit-btn" type="button" data-social-id="{{ $socialMedia['id'] }}">
                    {{ __('index.save') }}
                </button>
                <button class="btn btn-dark" type="button" onclick="window.location.reload();">{{ __('index.reset') }}</button>
            </div>
        </form>
    </div>
    @endforeach
</div>

<div class="modal fade" id="password-confirm-modal" tabindex="-1" role="dialog" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px">
        <div class="modal-content position-relative">
            <form id="password-confirm-form" action="{{ url('company/confirm-password-integration') }}" method="POST">
                @csrf
                <input type="hidden" id="current-social-id" value="">

                <div class="modal-body p-0">
                    <div class="rounded-top-3 py-3 ps-4 pe-6 bg-body-tertiary">
                        <h4 class="mb-1">Confirm Your Password</h4>
                        <p class="m-0">Please confirm your password to view the saved data</p>
                    </div>
                    <div class="p-4 pb-0">
                        <div class="mb-3">
                            <label class="col-form-label" for="user-password">Your Password:</label>
                            <input class="form-control" id="user-password" name="password" type="password" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="close-modal" type="button" data-bs-dismiss="modal">
                        Cancel </button>
                    <button class="btn btn-primary" type="submit" id="submit-password"> Submit </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
    var companyId = "{{ request('companyId') }}"
  </script>

   <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
  <script>
    $(document).ready(function() {
        var currentSocialId = '';

        $('.btn-show-hidden').on('click', function() {
            currentSocialId = $(this).data('social-id');
            $('#current-social-id').val(currentSocialId);
            $("#password-confirm-modal").modal('show');
        });

        $('#password-confirm-modal').on('shown.bs.modal', function () {
            $('#user-password').focus();
        });

        $("form#password-confirm-form").on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#password-confirm-modal").modal('hide');
                        fetchKeys(currentSocialId);
                    }
                },
                error: function(xhr) {
                    alert("Incorrect password");
                }
            });
        });

        function fetchKeys(socialId) {
            $.ajax({
                type: "GET",
                url: "{{ url('company/get-integration-keys') }}",
                data: {
                    company_id: companyId,
                    social_media_id: socialId
                },
                success: function(response) {
                    if (response.success) {
                        response.credentials.forEach(function(item) {
                            var $input = $('#value-' + item.id);
                            $input.attr('type', 'text');
                            $input.val(item.value);
                            $input.data('original-value', item.value);
                        });
                        // Disable show button for this card
                        $('.btn-show-hidden[data-social-id="' + socialId + '"]').prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        $("#password-confirm-modal").modal('show');
                    } else {
                        alert("Error fetching keys");
                    }
                }
            });
        }
    });
  </script>
 <script src="{{ asset('init/company/integration_credentials.js?v=' . time()) }}"></script>
@endpush
