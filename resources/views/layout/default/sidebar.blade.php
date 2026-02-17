<nav class="navbar navbar-light navbar-vertical navbar-expand-xl navbar-card">
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation">
                <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
            </button>
        </div>
        <a class="navbar-brand" href="{{ url('dashboard') }}">
            <div class="d-flex align-items-center py-3">
                {{-- <img class="me-2" src="assets/img/icons/spot-illustrations/falcon.png" alt="" width="40" /> --}}
                <span class="font-sans-serif">{{ config('app.name') }}</span>
            </div>
        </a>
    </div>
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                @haspermission('Dashboard.View', 'admin')
				<li class="nav-item">
                    <!-- parent pages-->
                    <a class="nav-link" href="{{ url('dashboard') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-chart-pie"></span>
                            </span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>
                </li>
				@endhaspermission
				@canany(['Role.List', 'PermissionGroup.List', 'Permissions.List','User.List'])
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#dashboard" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dashboard">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-cog"></span></span><span class="nav-link-text ps-1">{{ __('index.configuration') }}</span>
                        </div>
                    </a>
                    <ul class="nav collapse" id="dashboard">
                        @haspermission('Role.List', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/role') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-cog"></span><span class="nav-link-text ps-1">{{ __('index.roles') }}</span>
                                </div>
                            </a>
                        </li>
                        @endhaspermission
                        @haspermission('PermissionGroup.List', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/permission-groups') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions_groups') }}</span>
                                </div>
                            </a>
                        </li>
                        @endhaspermission
                        @haspermission('Permissions.List', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/permissions') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions') }}</span>
                                </div>
                            </a>
                        </li>
                        @endhaspermission
                        @haspermission('User.List', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/users') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.users') }}</span>
                                </div>
                            </a>
                        </li>
						 @endhaspermission
						  @haspermission('Subscription.List', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/subscription-plan') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.subscriptions') }}</span>
                                </div>
                            </a>
                        </li>
						 @endhaspermission
                         @haspermission('PaymentSetting.Create', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/payment-setting') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.payment_setting') }}</span>
                                </div>
                            </a>
                        </li>
						 @endhaspermission
                        @haspermission('AppSetting.Edit', 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/app-settings/list') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-cogs"></span><span class="nav-link-text ps-1">App Setting</span>
                                </div>
                            </a>
                        </li>
                        @endhaspermission
                    </ul>
                </li>
                @endcanany
				@haspermission('Social Platform.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('social-media-apps') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-users"></span>
                            </span>
                            <span class="nav-link-text ps-1"> {{ __('index.social_media_app') }}</span>
                        </div>
                    </a>
                </li>
                @endhaspermission
				@haspermission('Company.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('company') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-building"></span>
                            </span>
                            <span class="nav-link-text ps-1"> {{ __('index.company') }}</span>
                        </div>
                    </a>
                </li>
				@endif

                @canany(['MetaSetting.Edit'])
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#settings" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="settings">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-cog"></span>
                            </span>
                            <span class="nav-link-text ps-1"> {{ __('index.setting') }}</span>
                        </div>
                    </a>
                     @haspermission('MetaSetting.Edit', 'admin')
                    <ul class="nav collapse" id="settings">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('settings/meta') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-text ps-1">Meta (Fb + Insta)</span></div>
                            </a>
                        </li>
                    </ul>
                     @endhaspermission
                </li>
                @endcanany
            </ul>
        </div>
    </div>
</nav>
