<?php
/** --------------------------------------------------------------------------------
 * This controller manages all permission operations
 * - Listing permissions
 * - Adding new permissions
 * - Storing permissions in database
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;
use Spatie\Permission\Models\Role;
class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure user is logged in

        $this->middleware(function ($request, $next) {
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return $next($request);
            }

            $role_id = $user->role_id;
            $action = $request->route()->getActionMethod();

            $permissions = [
                'index' => 'Permissions.List',
                'add_permission' => 'Permissions.Create',
                'store' => 'Permissions.Create',
            ];

            if (array_key_exists($action, $permissions)) {
                if (!isRolePermission($role_id, $permissions[$action])) {
                    abort(403, 'You do not have the required permissions to access this page.');
                }
            }

            return $next($request);
        });
    }


    /**
	 * Display paginated list of permissions with their groups
	 */
    public function index()
    {
        try {
            $permissions = Permission::join('permission_groups', 'permission_groups.id', '=', 'permissions.group_id')->paginate(10);
            $groups = PermissionGroup::all();

            // Success log
            LogHelper::logSuccess(
                'success',
                'Permission index page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.configuration.permissions.index', compact('permissions', 'groups'));
        } catch (\Exception $ex) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while fetching permission',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while fetching permissions.');
        }
    }

   /**
	 * Show form to add new permission
	 */
    public function add_permission()
    {
        try {
            return view('main.configuration.permissions.add');

        } catch (\Exception $ex) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while loading add permission page',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while loading the add permission page.');
        }
    }


	/**
	 * Store new permission in database
	 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required',
            'section' => 'required',
            'group_id' => 'required'
        ], [
            'group.required' => 'Group is required.',
            'section.required' => 'Section is required.',
            'group_id.required' => 'Group ID is required.',
        ]);

       try {
            $permissionExists = Permission::where('name', $request->group . '.' . $request->section)->count();

            if ($permissionExists > 0) {
                // Error log for duplicate
                LogHelper::logError(
                    'exception',
                    'Duplicate permission attempted',
                    'Permission already exists: ' . $request->group . '.' . $request->section,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                return redirect('/configuration/permissions')->with('error', 'Similar permission already exists');
            }

            // Permission create
            $result = Permission::create([
                'group_id' => $request->group_id,
                'name' => $request->group . '.' . $request->section,
                'guard_name' => 'admin'
            ]);

            // Success log
            LogHelper::logSuccess(
                'success',
                'The Permission added successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null,
                ['id' => $result->id]
            );

            // Assign permission to default user (ID = 1)
           // $user = User::find(1);
            $role = Role::findOrFail(1);
            $role->givePermissionTo($request->group . '.' . $request->section);

            // Assign permission to default user (ID = 2)
            // $user = User::find(2);
             $role = Role::findOrFail(2);
             $role->givePermissionTo($request->group . '.' . $request->section);

            return redirect('/configuration/permissions')->with('success', 'Record added successfully');

        } catch (\Exception $ex) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while saving the permission',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null,
                $request->all()
            );

            return redirect()->back()->with('error', 'An error occurred while saving the permission.');
        }
    }
}
