<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Auth::guard('admin')->check()) {
                $user = Auth::guard('admin')->user();

                if ($user->is_active == 0 || $user->is_block == 1) {
                    Auth::guard("admin")->logout();
                    session()->forget('SUPERUSER_LOGIN');

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $request->session()->flash('success', 'Your account is inactive or blocked. Please contact the administrator.');

                    return redirect()->route('login');
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            LogHelper::logError(
                'exception',
                'Middleware => CheckUserStatus',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $request->path(),
                ''
            );

            return $next($request);
        }
    }
}
