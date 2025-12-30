<div class="card mb-4">
    <form id="document_form_update" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card-header">
            <h5 class="mb-0">{{ __('index.document_information') }}</h5>
        </div>
        <div class="card-body">
            <div id="documents-repeater">
                @php

                    if (empty($documents)) {
                        // Initialize with one empty document if none exist
                        $documents = [
                            [id => '', 'type' => '', 'number' => '', 'file_path' => '', 'is_existing' => false],
                        ];
                    }
                @endphp

                @foreach ($documents as $index => $document)
                    <div class="document-row mb-3 border-bottom pb-3">
                        <div class="row">
                            <!-- Document ID (if exists) -->

                            <!-- Hidden Document ID Field -->
                            <input type="hidden" name="documents[{{ $index }}][id]"
                                value="{{ $document['id'] ?? '' }}">

                            <!-- Document Type -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ __('index.document_type') }}</label>
                                <input type="text" class="form-control document-type"
                                    name="documents[{{ $index }}][type]"
                                    value="{{ old("documents.$index.type", $document['type'] ?? '') }}"
                                    placeholder="e.g. COI, PAN etc.">
                            </div>

                            <!-- Document Number -->
                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ __('index.document_number') }}</label>
                                <input type="text" class="form-control document-number"
                                    name="documents[{{ $index }}][number]"
                                    value="{{ old("documents.$index.number", $document['number'] ?? '') }}">
                            </div>

                            <!-- Document File -->
                            <div class="col-md-3 mb-2">
                                <label class="form-label">{{ __('index.document_file') }}</label>
                                <input type="file" class="form-control document-file"
                                    name="documents[{{ $index }}][file]" accept=".pdf,.jpg,.jpeg,.png">

                                <!-- Hidden field for existing file -->
                                <input type="hidden" name="documents[{{ $index }}][existing_file]"
                                    value="{{ $document['file_path'] ?? '' }}">

                                <!-- File Preview Container -->
                                <div class="file-preview mt-2">
                                    @if (!empty($document['file_path']))
                                        @if (Str::endsWith($document['file_path'], '.pdf'))
                                            <a href="{{ $document['file_path'] ? url('storage-bucket?path=' . $document['file_path'] . '&t=' . time()) : '' }}"
                                                target="_blank">
                                                <div class="pdf-preview">
                                                    <i class="fas fa-file-pdf text-danger fa-3x"></i>
                                                    <p class="small mb-0 text-black">
                                                        {{ basename($document['file_path']) }}</p>
                                                    <span class="badge bg-primary view-pdf-badge">View PDF</span>
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ $document['file_path'] ? url('storage-bucket?path=' . $document['file_path'] . '&t=' . time()) : '' }}"
                                                target="_blank">
                                                <img class="img-preview img-thumbnail"
                                                    src="{{ $document['file_path'] ? url('storage-bucket?path=' . $document['file_path'] . '&t=' . time()) : '' }}"
                                                    style="max-height: 100px;">
                                                <span class="badge bg-primary mt-1 view-image-badge">View Image</span>
                                            </a>
                                        @endif
                                        
                                    @else
                                        <div class="no-preview">
                                            <i class="fas fa-file-alt fa-3x text-secondary"></i>
                                            <p class="small mb-0">No file selected</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-document"
                                    @if ($loop->first && count($documents) === 1) style="display: none;" @endif>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
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
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-6 text-start">
                    <button type="button" id="document_update_prev" class="btn btn-outline-secondary">
                        {{ __('index.previous') }}
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="submit" id="documents_submit" class="btn btn-primary">
                        {{ __('index.update') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
