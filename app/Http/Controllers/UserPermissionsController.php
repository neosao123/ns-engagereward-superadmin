<?php
/** 
 * Controller for managing user permissions
 * 
 * This controller handles viewing and modifying permissions for users,
 * including direct permissions and permissions inherited from roles.
 * 
 * @author     Neosao Services Pvt Ltd.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;
use App\Helpers\LogHelper;

class UserPermissionsController extends Controller
{
    /**
     * Constructor - applies middleware for permissions
     */
    public function __construct()
    {
        // Only users with 'User.Permissions' or 'admin' permission can access index
        $this->middleware('permission:User.Permissions,admin')->only(['index']);
    }
    
    /**
     * Display the permissions management page for a specific user
     *
     * @param string $id The user ID to manage permissions for
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(string $id)
    {
        try {
            if ($id) {
                // Get user with their roles
                $user = User::with('roles')->where('id', $id)->first();
                
                // Get all permission groups that have permissions
                $groups = PermissionGroup::join('permissions', 'permission_groups.id', '=', 'permissions.group_id')
                    ->select('permission_groups.*')
                    ->distinct()
                    ->get();
                    
                // Get all permissions
                $permissions = Permission::get();

                if ($user) {
                    // Get user's direct permissions and permissions via roles
                    $directPermission = $user->getDirectPermissions();
                    $permissionsViaRoles = $user->getPermissionsViaRoles();
                    
                    return view('main.user-permissions.index', compact(
                        'id', 
                        'user', 
                        'groups', 
                        'permissions', 
                        'directPermission', 
                        'permissionsViaRoles'
                    ));
                }

                // Log error if user not found
                LogHelper::logError(
                    'error',
                    'Invalid user requested for permission list.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                return redirect('/user')->with('error', 'User was not found or got removed');
            }

            return redirect('/user')->with('error', 'Invalid URL. Please try again!');
        } catch (\Exception $ex) {
            // Log any exceptions that occur
            LogHelper::logError(
                'exception',
                'An error occurred while loading the permission list.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while loading the permission list.');
        }
    }

    /**
     * Grant or revoke a permission for a user
     *
     * @param mixed $id User ID
     * @param Request $r Request object containing:
     *   - permissionId: The ID of the permission to modify
     *   - mode: Either "revoke" to remove permission or any other value to grant
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPermission($id, Request $r)
    {
        try {
            $id = $r->id;
            $permissioId = $r->permissionId;
            $mode = $r->mode;
            $user = User::find($id);

            if ($user) {
                $permissions = Permission::where('id', $permissioId)->first();

                if ($mode === "revoke") {
                    // Revoke the permission from user
                    $user->revokePermissionTo($permissions);

                    // Log successful permission revocation
                    LogHelper::logSuccess(
                        'success',
                        'Permission removed from user successfully.',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        request()->path(),
                        Auth::guard('admin')->user()->id ?? null
                    );

                    return response()->json(['status' => 200, 'message' => 'Permission removed successfully'], 200);
                } else {
                    // Grant the permission to user
                    $user->givePermissionTo($permissions);

                    // Log successful permission grant
                    LogHelper::logSuccess(
                        'success',
                        'Permission granted to user successfully.',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        request()->path(),
                        Auth::guard('admin')->user()->id ?? null
                    );

                    return response()->json(['status' => 200, 'message' => 'Permission added successfully'], 200);
                }
            }

            // Log error if user not found
            LogHelper::logError(
                'error',
                'Invalid user requested for permission update.',
                'User not found or deleted.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json(['message' => 'Failed to apply permission.'], 200);
        } catch (\Exception $ex) {
            // Log any exceptions that occur
            LogHelper::logError(
                'exception',
                'An error occurred while applying the permission.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while applying the permission.');
        }
    }
}