@extends('layout.default.master', ['pageTitle' => __('settings.instagram_config')])
@push('styles')
@endpush
@section('content')
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fab fa-instagram" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative">
                    <span class="bg-200 dark__bg-1100 pe-3">@lang('settings.instagram_config')</span>
                    <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
                </h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item">
                                <a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">
                                    @lang('index.dashboard')</a>
                            </li>
                        @endif
                        <li class="breadcrumb-item">@lang('settings.settings')</li>
                        <li class="breadcrumb-item">@lang('settings.instagram_config')</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Success message --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    {{-- Error messages --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4 col-md-6" id="app-keys-info">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Instagram App Developer Keys</h6>
                        </div>
                        <button title="Update keys for instagram app details"
                            class="btn btn-primary btn-sm" id="btn-update-keys"><i class="far fa-edit"></i> Update</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div>APP ID</div>
                        <div class="input-group">
                            <span id="app-id-display" class="form-control">****************</span>
                            <button class="btn btn-outline-secondary btn-sm btn-cpy" type="button" title="Copy App ID">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div>APP Secret</div>
                        <div class="input-group">
                            <span id="app-secret-display" class="form-control">****************</span>
                            <button class="btn btn-outline-secondary btn-sm btn-cpy" type="button" title="Copy App Secret">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm btn-show-hidden" title="Show App Keys">
                        <i class="far fa-eye"></i> Show</button>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6" id="app-keys-form" style="display: none">
            <form id="form-app-keys" action="{{ url('settings/instagram/update-keys') }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">@lang('settings.instagram_app_keys')</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="mb-2">
                            <label for="app_id">App ID</label>
                            <input type="password" id="app_id" name="app_id" class="form-control" value=""
                                required />
                        </div>
                        <div class="mb-2">
                            <label for="app_secret">App Secret</label>
                            <input type="password" id="app_secret" name="app_secret" class="form-control" value=""
                                required />
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary btn-submit" data-form-id="form-app-keys" type="submit">
                            <i class="fas fa-save me-2"></i>@lang('index.submit')</button>
                        <button class="btn btn-outline-primary" type="button" id="btn-close-form"> Cancel </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="password-confirm-modal" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px">
            <div class="modal-content position-relative">
                <form id="password-confirm-form" action="{{ url('settings/instagram/confirm-password') }}" method="POST">
                    @csrf

                    <div class="modal-body p-0">
                        <div class="rounded-top-3 py-3 ps-4 pe-6 bg-body-tertiary">
                            <h4 class="mb-1" id="modalExampleDemoLabel">Confirm Your Password</h4>
                            <p class="m-0">Please confirm your password to view the saved data</p>
                        </div>
                        <div class="p-4 pb-0">
                            <div class="mb-3">
                                <label class="col-form-label" for="user-password">Your Password:</label>
                                <input class="form-control" id="user-password" name="password" type="password" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" id="close-modal" type="button" data-bs-dismiss="modal">
                            Cancel </button>
                        <button class="btn btn-primary" type="submit" id="submit"> Submit </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var passwordConfirmed = false;

            let instagramAccessTimeout;

            function startInstagramAccessTimer() {
                clearTimeout(instagramAccessTimeout);

                instagramAccessTimeout = setTimeout(function() {
                    $('#app-id-display').text('****************');
                    $('#app-secret-display').text('****************');
                    $('.btn-show-hidden').prop('disabled', false);
                }, 20000); // 20 seconds
            }

            $('button#close-modal').on('click', function() {
                var modalEl = document.getElementById('password-confirm-modal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            });

            $('.btn-show-hidden').on('click', function() {
                $("#password-confirm-modal").modal('show');
            });

            $("#password-confirm-modal").on('shown.bs.modal', function() {
                $('#user-password').trigger('focus');
            });

            $("button#btn-update-keys").on('click', function() {
                $("#app-keys-info").hide();
                $("#app-keys-form").show();
                $("#password-confirm-modal").modal('hide');
            });

            $("button#btn-close-form").on('click', function() {
                $("#app-keys-form").hide();
                $("#app-keys-info").show();
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
                            fetchInstagramKeys();
                        }
                    },
                    error: function(xhr) {
                        $("button.btn-show-hidden").removeAttr('disabled');
                        alert("Incorrect password");
                    }
                });
            });

            function fetchInstagramKeys() {
                $.ajax({
                    type: "GET",
                    url: "{{ url('settings/instagram/get-keys') }}",
                    success: function(response) {
                        $("button.btn-show-hidden").prop('disabled', true);
                        if (response.success) {
                            $('#app-id-display').text(response.app_id);
                            $('#app-secret-display').text(response.app_secret);
                            startInstagramAccessTimer();
                        }
                    },
                    error: function(xhr) {
                        $("button.btn-show-hidden").removeAttr('disabled');
                        if (xhr.responseJSON.expired) {
                            $("#password-confirm-modal").modal('show');
                        }
                    }
                });
            }

            $('.btn-cpy').on('click', function() {
                var text = $(this).siblings('span').text();
                if (text.includes('****')) {
                    alert("Please show the keys first.");
                    return;
                }
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(function() {
                        alert("Copied to clipboard!");
                    });
                } else {
                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert("Copied to clipboard!"); 
                }
            });
        });
    </script>
@endpush
