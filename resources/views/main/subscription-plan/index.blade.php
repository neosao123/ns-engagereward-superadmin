@extends('layout.default.master', ['pageTitle' => __('index.subscriptions')])

@push('styles')
    <link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />

    <style>
        tr.group,
        tr.group:hover {
            background-color: #ddd !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: transparent !important;
        }

        .error {
            color: red;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-layer-group" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <h5 class="mb-0 text-primary position-relative">
                <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.subscriptions') }}</span>
                <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ __('index.subscriptions') }}</li>
                </ol>
            </nav>
        </div>
        @if (Auth::guard('admin')->user()->can('Subscription.Create', 'admin'))
            <div class="col-auto ms-2 align-items-center">
                <a href="{{ url('subscription-plan/create') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1"><i
                        class="fas fa-plus me-1"></i>{{ __('index.create') }}</a>
            </div>
        @endif
    </div>

    <div class="row gx-3">

        <!-- Subscription Table -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-light d-flex">
                    <div class="col">
                        <h5 class="mb-0">{{ __('index.list') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table id="dt-subscriptions" class="table table-hover">
                            <thead>
                                <tr>
                                    @if (Auth::guard('admin')->user()->canany(['Subscription.Edit', 'Subscription.Delete', 'Subscription.View']))
                                        <th scope="col">{{ __('index.action') }}</th>
                                    @endif
                                    <th scope="col">{{ __('index.subscription_title') }}</th>
                                    <th scope="col">{{ __('index.months') }}</th>
                                    <th scope="col">{{ __('index.per_month_price') }}</th>
                                    <th scope="col">{{ __('index.discount_type') }}</th>
                                    <th scope="col">{{ __('index.discount_value') }}</th>
                                    <th scope="col">{{ __('index.total_price') }}</th>

                                    <th scope="col">{{ __('index.from_date') }}</th>
                                    <th scope="col">{{ __('index.to_date') }}</th>
                                    <th scope="col">{{ __('index.status') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var baseUrl = "{{ url('/') }}";
        var csrfToken = "{{ csrf_token() }}";
    </script>

    <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>

    <script src="{{ asset('init/subscription-plan/index.js?v=' . time()) }}"></script>
@endpush
