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
                @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
				{{-- @haspermission('Dashboard.View', 'admin') --}}
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
				@endif
				{{-- @endhaspermission --}}
				@if(isRolePermission(auth()->user()->role_id, 'Role.List') || isRolePermission(auth()->user()->role_id, 'PermissionGroup.List') || isRolePermission(auth()->user()->role_id, 'Permissions.List') || isRolePermission(auth()->user()->role_id, 'User.List') || isRolePermission(auth()->user()->role_id, 'Subscription.List') || isRolePermission(auth()->user()->role_id, 'PaymentSetting.Create') || isRolePermission(auth()->user()->role_id, 'AppSetting.Edit'))
                {{-- @canany(['Role.List', 'PermissionGroup.List', 'Permissions.List','User.List']) --}}
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#dashboard" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dashboard">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-cog"></span></span><span class="nav-link-text ps-1">{{ __('index.configuration') }}</span>
                        </div>
                    </a>
                    <ul class="nav collapse" id="dashboard">
                        @if(isRolePermission(auth()->user()->role_id, 'Role.List'))
                        {{-- @haspermission('Role.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/role') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-cog"></span><span class="nav-link-text ps-1">{{ __('index.roles') }}</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @endhaspermission --}}
                         @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                        {{-- @haspermission('PermissionGroup.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/permission-groups') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions_groups') }}</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @endhaspermission --}}
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                        {{-- @haspermission('Permissions.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/configuration/permissions') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions') }}</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @endhaspermission --}}
                        @if(isRolePermission(auth()->user()->role_id, 'User.List'))
                        {{-- @haspermission('User.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/users') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.users') }}</span>
                                </div>
                            </a>
                        </li>
						 @endif
						 {{-- @endhaspermission --}}
						  @if(isRolePermission(auth()->user()->role_id, 'Subscription.List'))
                          {{-- @haspermission('Subscription.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/subscription-plan') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.subscriptions') }}</span>
                                </div>
                            </a>
                        </li>
						 @endif
						 {{-- @endhaspermission --}}
                         @if(isRolePermission(auth()->user()->role_id, 'PaymentSetting.Create'))
                         {{-- @haspermission('PaymentSetting.Create', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/payment-setting') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.payment_setting') }}</span>
                                </div>
                            </a>
                        </li>
						 @endif
						 {{-- @endhaspermission --}}
                        @if(isRolePermission(auth()->user()->role_id, 'AppSetting.List'))
                        {{-- @haspermission('AppSetting.List', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/app-settings/list') }}" data-bs-toggle="" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-cogs"></span><span class="nav-link-text ps-1">App Setting</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @endhaspermission --}}
                    </ul>
                </li>
                @endif
                {{-- @endcanany --}}
				@if(isRolePermission(auth()->user()->role_id, 'Social Platform.List'))
                {{-- @haspermission('Social Platform.List', 'admin') --}}
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
                @endif
                {{-- @endhaspermission --}}
				@if(isRolePermission(auth()->user()->role_id, 'Company.List'))
                {{-- @haspermission('Company.List', 'admin') --}}
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
                {{-- @endhaspermission --}}

                @if(isRolePermission(auth()->user()->role_id, 'Template.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('templates') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-file-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1"> Template</span>
                        </div>
                    </a>
                </li>
                @endif
                {{-- @endhaspermission --}}

                @if(isRolePermission(auth()->user()->role_id, 'MetaSetting.Edit') || isRolePermission(auth()->user()->role_id, 'InstagramSetting.Edit'))
                {{-- @canany(['MetaSetting.Edit', 'InstagramSetting.Edit']) --}}
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#settings" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="settings">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-cog"></span>
                            </span>
                            <span class="nav-link-text ps-1"> {{ __('index.setting') }}</span>
                        </div>
                    </a>
                    <ul class="nav collapse" id="settings">
                        @if(isRolePermission(auth()->user()->role_id, 'MetaSetting.Edit'))
                        {{-- @haspermission('MetaSetting.Edit', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('settings/meta') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-text ps-1">Meta (Fb + Insta)</span></div>
                            </a>
                        </li>
                         @endif
                         {{-- @endhaspermission --}}
                         @if(isRolePermission(auth()->user()->role_id, 'InstagramSetting.Edit'))
                         {{-- @haspermission('InstagramSetting.Edit', 'admin') --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('settings/instagram') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-text ps-1">Instagram</span></div>
                            </a>
                        </li>
                         @endif
                         {{-- @endhaspermission --}}
                    </ul>
                </li>
                @endif
                {{-- @endcanany --}}
            </ul>
        </div>
    </div>
</nav>
