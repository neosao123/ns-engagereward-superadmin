<?php
/** --------------------------------------------------------------------------------
 * This controller manages all dasboard operations
 *   
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Helper
use App\Helpers\LogHelper;
use DB;
use App\Models\Company;
use App\Models\SocialMediaApp;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
class DashboardController extends Controller
{
	
	public function __construct()
    {
        // List & index
        //$this->middleware('permission:Dashboard.View,admin')->only(['index']);
		//$this->middleware('permission:Welcome.View,admin')->only(['welcome']);


		$this->middleware('auth'); // Ensure user is logged in

        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            if (!$this->user) {
                return $next($request);
            }

            $role = Role::find($this->user->role_id);
            $action = $request->route()->getActionMethod();

            $permissions = [
                'index' => 'Dashboard.View',
                'welcome' => 'Welcome.View'
            ];

            if (array_key_exists($action, $permissions)) {
                if (!$role || !$role->hasPermissionTo($permissions[$action], 'admin')) {
                    abort(403, 'You do not have the required permissions to access this page.');
                }
            }
            return $next($request);
        });
	}
	
	/**
     * Display the main dashboard with statistics
     * 
     * This method shows the admin dashboard with company and social media app counts.
     * It performs permission checks and logs access attempts.
     *
     * @param Request $r The incoming request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index( Request $r ) {
		
		try{
		
			$admin = Auth::guard('admin')->user();

			// Check permissions
			if (!in_array($admin->role_id, [1, 2]) && !isRolePermission($admin->role_id, 'Dashboard.View')) {
			//if ($admin->role_id != 1 && !$admin->can('Dashboard.View')) {
				LogHelper::logSuccess(
					'success',
					'dashboard.welcome_page_success',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					''
				);
				return redirect('welcome');
			}
			
			$company=Company::whereNull("deleted_at")->count();
			$SocialMediaApp=SocialMediaApp::whereNull("deleted_at")->count();
			return view("main.dashboard.index",compact("company","SocialMediaApp"));
		}catch (\Exception $exception) {
			LogHelper::logError(
				'exception',
				'An error occurred while loading the dashboard.',
				$exception->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			// Optional: redirect or return error view
			return redirect()->back()->withErrors('Something went wrong while loading the dashboard.');
		}
	}
	 /**
     * Display the welcome page
     * 
     * This is the fallback page shown to users who don't have dashboard permissions.
     * By default, all users have access to this page (permission check is in middleware).
     *
     * @param Request $r The incoming request
     * @return \Illuminate\View\View
     */
	public function welcome(Request $r){
		return view("welcome");
	}
}
