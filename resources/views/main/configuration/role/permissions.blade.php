@extends('layout.default.master', ['pageTitle' => __('index.role_permissions')])
@push('styles')
@endpush
@section('content')
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative"><span
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.role_wise_permissions') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        @if (Auth::guard('admin')->user()->can('Role.List', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('configuration/role') }}"
                                    class="text-decoration-none text-dark">{{ __('index.roles') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.permissions') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if (Auth::guard('admin')->user()->can('Role.List', 'admin')) 
            <div class="col-auto ms-2 align-items-center">
                <a href="{{ url('configuration/role') }}"
                    class="btn btn-outline-secondary btn-sm me-1 mb-1">{{ __('index.back') }}</a>
            </div>
        @endif
    </div>
    <!--ADD USER-->
    <div class="row gx-3">
        <!-- List Permissions -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><strong>{{ $role->name }}</strong> {{ __('index.permissions') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row g-3" data-masonry='{"percentPosition": true }'>
                @php
                    $roles_permissions = $role_has_permissions->pluck('id')->toArray();
                @endphp
                @foreach ($groups as $group) 
                    <div class="col-sm-12 col-md-4 col-lg-3">
                        <div class="card" id="accordion-{{ $group->id }}">
                            <div class="card-header bg-light">
                                <strong class="mb-0">
                                    {{ $group->group_name }}
                                </strong>
                            </div>
                            <div class="card-body"> 
                                @foreach ($permissions as $permission)
                                    @php
                                        $isChecked = in_array($permission->id, $roles_permissions);
                                    @endphp
                                    @if ($permission->group_id === $group->id)
                                        <div
                                            class="d-flex justify-content-between btn-reveal-trigger border-200 todo-list-item">
                                            <div class="form-check mb-0 d-flex align-items-center">
                                                <input type="checkbox"
                                                    class="form-check-input rounded-circle p-1 form-check-input-primary"
                                                    data-permission-id="{{ $permission->id }}"
                                                    name="{{ $permission->name }}" id="{{ 'chk-' . $permission->id }}"
                                                    {{ $isChecked ? 'checked' : '' }} />
                                                <label class="form-check-label mb-0 mt-1 p-1"
                                                    for="{{ 'chk-' . $permission->id }}">{{ str_replace($group->group_name . '.', '', $permission->name) }}</label>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var baseUrl = "{{ url('/') }}";
        const roleId = '{{ $roleId }}';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"
        integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async>
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>
    <script src="{{ asset('init/role/permission.js?v=' . time()) }}"></script>
@endpush
