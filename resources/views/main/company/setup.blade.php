@extends('layout.default.master', ['pageTitle' => 'SaaS Setup'])
@section('content')
    <div class="d-flex my-3">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">SaaS Setup</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                class="text-decoration-none text-dark">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Company</li>
                        <li class="breadcrumb-item active" aria-current="page">SaaS Setup</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="col-auto ms-2 align-items-center">
            <a href="{{ url('company') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center me-1 mb-1">
                <span class="me-2 fas fa-chevron-left"></span>Back</a>
        </div>

    </div>
    <div class="row g-3">
        <div class="col-12">
            <div class="card mb-3">
                @if ($company->site_setup == 0)
                    <div class="card-header bg-secondary text-white">
                        <div><strong>Company SaaS Setup</strong></div>
                        <p class="mb-0">Please complete all setup steps so that company site will be configured and
                            installed.</p>
                    </div>
                @elseif ($company->site_setup == 1)
                    <div class="card-header bg-warning text-white">
                        <div><strong>Company SaaS Setup</strong></div>
                        <p class="mb-0">Please complete all setup steps so that company site will be configured and
                            installed.</p>
                    </div>
                @else
                    <div class="card-header bg-success text-white">
                        <div><strong>Company SaaS Setup Successfull</strong></div>
                        <p class="mb-0"> Company SaaS setup and installation is completed </p>
                    </div>
                @endif

                <div class="card-body p-2">
                    <h4 class="px-2">{{ $company->company_name }}</h4>
                    <ul class="mb-0 list-unstyled">
                        <li class="alert mb-0 rounded-0 py-2 px-card greetings-item border-top border-x-0 border-top-0">
                            <div class="row flex-between-center">
                                <div class="col">
                                    <div class="d-flex">
                                        <div class="fas fa-circle mt-1 fs--2"></div>
                                        <p class="fs--1 ps-2 mb-0"><strong class="me-3">Key
                                            </strong>{{ $company->company_key }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="alert mb-0 rounded-0 py-2 px-card greetings-item border-top border-x-0 border-top-1">
                            <div class="row flex-between-center">
                                <div class="col">
                                    <div class="d-flex">
                                        <div class="fas fa-circle mt-1 fs--2"></div>
                                        <p class="fs--1 ps-2 mb-0"><strong
                                                class="me-3">Email</strong>{{ $company->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="alert mb-0 rounded-0 py-2 px-card greetings-item border-top border-x-0 border-top-1">
                            <div class="row flex-between-center">
                                <div class="col">
                                    <div class="d-flex">
                                        <div class="fas fa-circle mt-1 fs--2"></div>
                                        <p class="fs--1 ps-2 mb-0"><strong class="me-3">Phone</strong>
                                            {{ $company->phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="card-body" id="sec-loading" style="display: none">
                    <div style="height:300px" class="d-flex flex-column align0-items-center justify-content-center">
                        <div class="mb-3">
                            <span class="fas fa-spinner fa-spin fa-2x"></span>
                        </div>
                        <div>Processing...</div>
                    </div>
                </div>

                <div class="card-body p-4" id="sec-action" style="display:block">
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const baseUrl = "{{ url('/') }}";
        const csrfToken = "{{ csrf_token() }}";
        const company_id = "{{ $company->id }}";
        const sec_loading = $("#sec-loading");
        const sec_action = $("#sec-action");

        function loadSteps() {
            $.ajax({
                url: baseUrl + '/company/' + company_id + '/setup/action',
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    sec_loading.show();
                    sec_action.empty();
                },
                success: function(response) {
                    sec_action.html(response.html);
                },
                complete: function() {
                    sec_loading.hide();
                    sec_action.show();
                }
            });
        }

        $(document).on('click', 'button#step_one_btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            $.ajax({
                url: baseUrl + '/api/v1/onboard/step-one',
                type: 'GET',
                data: {
                    company_id: company_id
                },
                dataType: 'json',
                beforeSend: function() {
                    btn.attr("disabled", true);
                    btn.html('<span class="fas fa-spinner fa-spin fa-2x me-2"></span><span>Processing...</span>');
                },
                success: function(response) {
                    console.log("step - one", response);
                    toast(response.msg, "success", 3)
                    setTimeout(() => {
                        loadSteps();
                    }, 3100);
                },
                error: function(error) {
                    btn.removeAttr("disabled");
                    btn.html("Try Again");
                },
            });
        });

        $(document).on('click', 'button#step_two_btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            $.ajax({
                url: baseUrl + '/api/v1/onboard/step-two',
                type: 'GET',
                data: {
                    company_id: company_id
                },
                dataType: 'json',
                beforeSend: function() {
                    btn.attr("disabled", true);
                    btn.html('<span class="fas fa-spinner fa-spin fa-2x me-2"></span><span>Processing...</span>');
                },
                success: function(response) {
                    console.log("step - two", response);
                    toast(response.msg, "success", 3)
                    setTimeout(() => {
                        loadSteps();
                    }, 3100);
                },
                error: function(error) {
                    btn.removeAttr("disabled");
                    btn.html("Try Again");
                },
            });
        });

        $(document).on('click', 'button#step_three_btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            $.ajax({
                url: baseUrl + '/api/v1/onboard/step-three',
                type: 'GET',
                data: {
                    company_id: company_id
                },
                dataType: 'json',
                beforeSend: function() {
                    btn.attr("disabled", true);
                    btn.html('<span class="fas fa-spinner fa-spin fa-2x me-2"></span><span>Processing...</span>');
                },
                success: function(response) {
                    console.log("step - three", response);
                    toast(response.msg, "success", 3)
                    setTimeout(() => {
                        loadSteps();
                    }, 3100);
                },
                error: function(error) {
                    btn.removeAttr("disabled");
                    btn.html("Try Again");
                },
            });
        });

        $(document).on('click', 'button#step_four_btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            $.ajax({
                url: baseUrl + '/api/v1/onboard/step-four',
                type: 'GET',
                data: {
                    company_id: company_id
                },
                dataType: 'json',
                beforeSend: function() {
                    btn.attr("disabled", true);
                    btn.html('<span class="fas fa-spinner fa-spin fa-2x me-2"></span><span>Processing...</span>');
                },
                success: function(response) {
                    console.log("step - four", response);
                    toast(response.msg, "success", 3)
                    setTimeout(() => {
                        loadSteps();
                    }, 3100);
                },
                error: function(error) {
                    btn.removeAttr("disabled");
                    btn.html("Try Again");
                },
            });
        });


        $(document).on('click', 'button#step_five_btn', function(e) {

            e.preventDefault();
            $.ajax({
                url: baseUrl + '/api/v1/onboard/step-five',
                type: 'GET',
                data: {
                     company_id: company_id
                },
                dataType: 'json',
                beforeSend: function() {
                    sec_loading.show();
                    sec_action.hide();
                },
                success: function(response) {
                    console.log("step - five", response);
                    loadSteps();
                },
                error: function(error) {
                    console.log("hiii", error);
                    toast(error?.responseJSON?.msg || "Something Went Wrong", "error", 3)
                    sec_loading.hide();
                    sec_action.show();
                },
            });
        });


        $(document).ready(function() {
            loadSteps();
        });
    </script>
@endpush
