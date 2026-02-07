@if ($steps)
    @php
        $step_status = [
            'step_one' => 'pending',
            'step_two' => 'pending',
            'step_three' => 'pending',
            'step_four' => 'pending',
            'step_five' => 'pending',
        ];

        // Assign step statuses from DB
        foreach ($steps as $step) {
            $step_status[$step->step_name] = $step->status;
        }

        // Function to get timeline class
        function getStepClass($status)
        {
            return $status == 'complete' ? 'timeline-past' : 'timeline-current';
        }

        $all_complete = collect($step_status)->every(fn($s) => $s == 'complete');
    @endphp

    {{-- STEP ONE --}}
    <div class="row g-3 timeline timeline-warning pb-card {{ getStepClass($step_status['step_one']) }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-secondary fas fa-cogs"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 border-bottom pb-card">
                <div class="col">
                    <h6 class="text-800 mb-1">Installation & Setup</h6>
                    <p class="fs--1 text-600 mb-0">Company account setup, folder creations, merging files, libraries
                        installation, etc...</p>
                </div>
                <div class="col-auto">
                    @if ($step_status['step_one'] == 'complete')
                        <p class="fs--2 text-500 mb-0">
                            {{ $steps->where('step_name', 'step_one')->first()->created_at ?? '' }}</p>
                    @else
                        <button class="btn btn-warning" id="step_one_btn">Install</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STEP TWO --}}
    <div class="row g-3 timeline timeline-warning pb-card {{ getStepClass($step_status['step_two']) }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-info fas fa-file-code"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 border-bottom pb-card">
                <div class="col">
                    <h6 class="text-800 mb-1">Environment Configuration</h6>
                    <p class="fs--1 text-600 mb-0">Environment setup, permissions, installing dependencies, updates,
                        etc...</p>
                </div>
                <div class="col-auto">
                    @if ($step_status['step_two'] == 'complete')
                        <p class="fs--2 text-500 mb-0">
                            {{ $steps->where('step_name', 'step_two')->first()->created_at ?? '' }}</p>
                    @elseif($step_status['step_one'] == 'complete')
                        <button class="btn btn-warning" id="step_two_btn">Install</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STEP THREE --}}
    <div class="row g-3 timeline timeline-warning pb-card {{ getStepClass($step_status['step_three']) }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-warning fas fa-database"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 border-bottom pb-card">
                <div class="col">
                    <h6 class="text-800 mb-1">Database</h6>
                    <p class="fs--1 text-600 mb-0">Setting up database, creating tables, views, relations etc..</p>
                </div>
                <div class="col-auto">
                    @if ($step_status['step_three'] == 'complete')
                        <p class="fs--2 text-500 mb-0">
                            {{ $steps->where('step_name', 'step_three')->first()->created_at ?? '' }}</p>
                    @elseif($step_status['step_one'] == 'complete' && $step_status['step_two'] == 'complete')
                        <button class="btn btn-warning" id="step_three_btn">Install</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STEP FOUR --}}
    <div class="row g-3 timeline timeline-warning pb-card {{ getStepClass($step_status['step_four']) }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-primary fas fa-fill-drip"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 border-bottom pb-card">
                <div class="col">
                    <h6 class="text-800 mb-1">Database Migrations & Records</h6>
                    <p class="fs--1 text-600 mb-0">Filling database with records and default options, theme setup,
                        setting up login access, etc.</p>
                </div>
                <div class="col-auto">
                    @if ($step_status['step_four'] == 'complete')
                        <p class="fs--2 text-500 mb-0">
                            {{ $steps->where('step_name', 'step_four')->first()->created_at ?? '' }}</p>
                    @elseif(
                        $step_status['step_one'] == 'complete' &&
                            $step_status['step_two'] == 'complete' &&
                            $step_status['step_three'] == 'complete')
                        <button class="btn btn-warning" id="step_four_btn">Install</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STEP FIVE --}}
    @php
        $companyCode = $company->company_unqiue_code;

        // Get APP_URL from environment
        $appUrl = env('ADMIN_API_URL');

        // Parse the URL to extract domain parts
        $parsedUrl = parse_url($appUrl);
        $host = $parsedUrl['host'] ?? '';

        // Extract the subdomain (e.g., 'superadmin' from 'superadmin.engagereward.com')
        $hostParts = explode('.', $host);
        $subdomain = $hostParts[0]; // 'superadmin', 'dashboard', etc.

        // Build the directory name dynamically
        // Format: engagereward-{subdomain}
        $directoryName = env('ADMIN_SITE_USER');

        // Build the full domain (e.g., 'dashboard.engagereward.com')
        $fullDomain = $host;

        // Create the dynamic cron command
        $cron_command = sprintf(
            '/usr/bin/php8.2 /home/%s/htdocs/%s/%s/appsource/artisan schedule:run >> /dev/null 2>&1',
            $directoryName,
            $fullDomain,
            strtolower($companyCode),
        );
    @endphp
    <div class="row g-3 timeline timeline-warning pb-card {{ getStepClass($step_status['step_five']) }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-primary fas fa-copy"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 border-bottom pb-card align-items-center">
                <div class="col">
                    <h6 class="text-800 mb-2">Set up Cron Job</h6>
                    <p class="fs--1 text-600 mb-2">Copy this cron command and set it up for SMS sending etc.</p>

                    <div class="input-group input-group-sm" style="max-width: 1300px;">
                        <input type="text" id="cronCommand" class="form-control" value="{{ $cron_command }}"
                            readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyCron()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <!-- Instructions -->
                    <ol class="fs--1 text-600 mb-2">
                        <li>Copy the cron command above.</li>
                        <li>Click “Add Cron Job” in your hosting/server panel.</li>
                        <li>Fill the cron details as required.</li>
                        <li>Click “Next” to go to the final “Setup Complete” step.</li>
                    </ol>

                    <!-- Preview image -->
                    <div class="mb-2">
                        <p class="fs--1 text-600 mb-1">Preview of the cron panel:</p>
                        <img src="{{ asset('img/cron-job.png') }}" alt="Cron Panel Preview"
                            class="img-fluid border rounded" style="max-width: 600px;">
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    @if (
                        $step_status['step_one'] == 'complete' &&
                            $step_status['step_two'] == 'complete' &&
                            $step_status['step_three'] == 'complete' &&
                            $step_status['step_four'] == 'complete')
                        @if ($step_status['step_five'] != 'complete')
                            <button class="btn btn-warning" id="step_five_btn">Submit</button>
                        @else
                            <p class="fs--2 text-500 mb-0">
                                {{ $steps->where('step_name', 'step_five')->first()->created_at ?? '' }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- FINAL SETUP COMPLETE --}}
    <div class="row g-3 timeline timeline-primary pb-card {{ $all_complete ? 'timeline-past' : '' }}">
        <div class="col-auto ps-4 ms-2">
            <div class="ps-2">
                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                    <span class="text-{{ $all_complete ? 'success' : 'dark' }} fas fa-flag-checkered"></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row gx-0 align-items-center">
                <div class="col">
                    <h6 class="text-800 mb-1">Setup Complete</h6>
                    <p class="fs--1 text-600 mb-0">
                        Installation and Setup has been successfully done...
                    </p>
                </div>

                <div class="col-auto text-end">
                    <p class="fs--2 text-700 mb-1">
                        {{ $all_complete ? 'Complete' : 'Waiting....' }}
                    </p>
                     @if (
                        $step_status['step_one'] == 'complete' &&
                            $step_status['step_two'] == 'complete' &&
                            $step_status['step_three'] == 'complete' &&
                            $step_status['step_four'] == 'complete')

                        <button class="btn btn-warning btn-sm" id="step_five_btn">
                        Send Mail
                        </button>

                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    function copyCron() {
        var copyText = document.getElementById("cronCommand");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Cron command copied: " + copyText.value);
    }
</script>
