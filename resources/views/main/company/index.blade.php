@extends('layout.default.master', ['pageTitle' => __('index.company')])

@push('styles')
 <link href="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
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
        <i class="fa-inverse fa-stack-1x text-primary fas fa-building" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <h5 class="mb-0 text-primary position-relative">
            <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.company') }}</span>
            <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
        </h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
               @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
			   <li class="breadcrumb-item">
                    <a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a>
                </li>
				@endif
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.company') }}</li>
            </ol>
        </nav>
    </div>
	 @if(Auth::guard('admin')->user()->can('Company.Create', 'admin'))
	 <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('company/create' ) }}" class="btn btn-falcon-primary btn-sm me-1 mb-1"><i class="fas fa-plus me-1"></i>{{ __('index.create') }}</a>
    </div>
	 @endif
</div>

<div class="row gx-3">
    <!-- Filters -->
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body row">


                <!-- Company Name -->
                <div class="col-lg-3 col-md-6 col-sm-12 pb-2">
                    <label class="form-label">{{ __('index.company_name') }}</label>
                    <select class="form-control select2 custom-select" id="company_name" name="company_name" style="width:100%"></select>
                </div>

                <!-- Email -->
                <div class="col-lg-3 col-md-6 col-sm-12 pb-2">
                    <label class="form-label">{{ __('index.email') }}</label>
                    <select class="form-control select2 custom-select" id="email" name="email" style="width:100%">
					</select>
                </div>

                <!-- Phone -->
                <div class="col-lg-3 col-md-6 col-sm-12 pb-2">
                    <label class="form-label">{{ __('index.phone') }}</label>
                    <select class="form-control select2 custom-select" id="phone" name="phone" style="width:100%"></select>
                </div>

                <!-- Filter Buttons -->
                <div class="col-12 d-flex justify-content-between mt-3">
                    <div>
                        @if(Auth::guard('admin')->user()->can('Company.Export', 'admin'))
						<button type="button" id="btnExcelDownload"
										class="btn btn-sm btn-outline-secondary me-1">
									<i class="fas fa-file-excel me-1"></i> {{ __('index.export_to_csv') }}
						</button>
						<button type="button" id="btnPdfDownload"
								class="btn btn-sm btn-outline-secondary me-1">
							<i class="fas fa-file-pdf me-1"></i> {{ __('index.export_to_pdf') }}
						</button>
                       @endif
                    </div>
                    <div>
                      	<!-- Buttons for Search and Clear -->
							<button class="btn btn-sm btn-primary me-1" id="search_filter">
								<i class="fas fa-search me-1"></i> {{ __('index.search') }}
							</button>
							<button class="btn btn-sm btn-outline-dark" id="reset_filter">
								<i class="fas fa-undo me-1"></i> {{ __('index.reset') }}
							</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Table -->
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive scrollbar">
                    <table id="dt-company" class="table table-hover">
                        <thead>
                            <tr>
							    @if(Auth::guard('admin')->user()->canany(['Company.Edit','Company.Delete','Company.View']))
                                <th scope="col">{{ __('index.action') }}</th>
							    @endif
                                 <th scope="col">{{ __('index.company_code') }}</th>
                                <th scope="col">{{ __('index.company_name') }}</th>

                                <th scope="col">{{ __('index.email') }}</th>
                                <th scope="col">{{ __('index.phone_number') }}</th>

								<th scope="col">{{ __('index.account_status') }}</th>
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
{{-- Modal For Change Status of company --}}
<div class="modal fade" id="accountStatusModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabel">Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 pb-2">
				<label for="status" class="form-label">{{__('index.status')}}</label>
				 <select class="form-select" id="account_status" name="account_status">
					<option value="">Select Status</option>
					<option value="active">Active</option>
					<option value="suspended">Suspended</option>
				</select>
			</div>
			<div class="col-lg-12 col-md-12 col-sm-12 pb-2" style="display:none;">
				<label class="form-label">{{__('index.reason')}}</label>
				<textarea class="form-control" name="reason" id="reason"></textarea>
			</div>
		 </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="button" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";

</script>
<script>
document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-copy-url')) {
        e.preventDefault();
        const btn = e.target.closest('.btn-copy-url');
        const url = btn.getAttribute('data-url');

        if (!url) {
            toast('No URL found to copy!', 'error');
            return;
        }

        navigator.clipboard.writeText(url)
            .then(function () {
                toast('Login URL copied to clipboard!', 'success');
            })
            .catch(function () {
                toast('Failed to copy URL!', 'error');
            });
    }
});
</script>


 <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
   <script src="{{ asset('vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
 <script src="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('init/company/index.js?v=' . time()) }}"></script>
@endpush
