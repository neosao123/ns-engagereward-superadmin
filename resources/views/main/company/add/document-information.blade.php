<form id="document_form_add" method="post" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3">{{ __('index.document_information') }}</h5>

                <!-- Documents Repeater Container -->
                <div id="documents-repeater">
                    <!-- Initial Document Row -->
                    <div class="document-row mb-3 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ __('index.document_type') }}  <span class="text-danger">*</span></label>
                                <input type="text" class="form-control document-type" name="documents[0][type]" placeholder="e.g. COI, PAN etc.">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ __('index.document_number') }}  <span class="text-danger">*</span></label>
                                <input type="text" class="form-control document-number" name="documents[0][number]">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">{{ __('index.document_file') }}  <span class="text-danger">*</span></label>
                                <input type="file" class="form-control document-file" name="documents[0][file]" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-preview mt-2">
                                    <img class="img-preview img-thumbnail" src="{{url('img/docs-placeholder.png')}}" style="max-height: 100px;">
                                    <div class="pdf-preview" style="display: none;">
                                        <i class="fas fa-file-pdf text-danger fa-3x"></i>
                                        <p class="small mb-0 text-black"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-document" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add More Button -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" id="add-document" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-2"></i> {{ __('index.add') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer py-3 px-sm-3 px-md-5 bg-light">
        <div class="row g-2">
            <div class="col-6 text-start">
                <div class="mb-0">
                    <button type="button" id="document_add_prev" class="btn btn-sm btn-outline-secondary">{{ __('index.previous') }}</button>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="mb-0">
                    <button type="button" id="documents_submit" class="btn btn-sm btn-primary">
                        <span class="fas fa-spinner fa-spin d-none" id="documents_submit_spinner"></span>
                        <span id="documents_submit_text">{{ __('index.submit') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
