<?php
/** --------------------------------------------------------------------------------
 * This controller manages all permission group
 *   
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;

class PermissionGroupController extends Controller
{
   
    /**
	 * Display paginated list of permission groups
	 * 
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
    public function index()
    {
        try {
            $permissionGroups = PermissionGroup::paginate(15);

            LogHelper::logSuccess(
                'success',
                'Permission group list loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                auth()->id()
            );

            return view('main.configuration.permission-groups.index', compact('permissionGroups'));
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while fetching permission group.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                auth()->id()
            );

            return redirect()->back()->with('error', 'An error occurred while fetching permission group.');
        }
    }
    

	/**
	 * Store new permission group in database
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */

    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|max:100',
            'slug' => 'required|unique:permission_groups',
        ],[
            'group_name.required' => 'Group name is required.',
            'group_name.max' => 'Group name must not exceed 100 characters.',
            'slug.required' => 'Slug is required.',
            'slug.unique' => 'Slug has already been taken.',
        ]);

        try {
			 $data = $request->only(['group_name', 'slug']); 
            $result = PermissionGroup::create($data);

            LogHelper::logSuccess(
                'success',
                'Permission group added successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                auth()->id()
            );

            return redirect('configuration/permission-groups')
                ->with('success', 'Permission Group created successfully.');
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while saving the permission group.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                auth()->id()
            );

            return redirect()->back()->with('error', 'An error occurred while saving the permission group.');
        }
    }
}
