<?php

/** --------------------------------------------------------------------------------
 * This controller manages all the role operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Classes\ActivityLog;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Helpers\LogHelper;
class RoleController extends Controller
{

	public function __construct()
    {
        // List & index
        $this->middleware('permission:Role.List,admin')->only(['List','index']);
		$this->middleware('permission:Role.Edit,admin')->only(['edit']);
		$this->middleware('permission:Role.Create-Update,admin')->only(['update']);
		$this->middleware('permission:User.Delete,admin')->only('destroy');

	}

	  /**
	 * Display the role management index page
	 *
	 * Shows the main role configuration view and handles:
	 * - Successful page loading with logging
	 * - Error cases with appropriate logging and user feedback
	 *
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	  public function index()
	{
		try {

			LogHelper::logSuccess(
				'success',
				'Role index page loaded successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return view('main.configuration.role.index');

		} catch (\Exception $exception) {

			LogHelper::logError(
				'exception',
				'An error occurred while loading the role index page.',
				$exception->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return redirect()->back()->with('error', 'An error occurred while loading the role index page.');
		}
	}


	/**
	 * Get paginated list of roles for DataTables
	 *
	 * Handles server-side processing for role management table with:
	 * - Search functionality
	 * - Pagination
	 * - Action buttons (edit/delete) with permission checks
	 * - DataTables compatible JSON response
	 *
	 * @param Request $r The incoming DataTables request
	 * @return \Illuminate\Http\JsonResponse
	 */

	public function list(Request $r)
	{
		try {
			$search = $r->input('search.value');
			$limit = $r->length;
			$offset = $r->start;
			$srno = $offset + 1;
			$dataCount = 0;
			$data = [];

			// Role List
			$result = Role::where(function ($query) use ($search) {
				$query->where('name', 'LIKE', '%' . $search . '%');
			})->orderBy('id', 'desc')->limit($limit)->skip($offset)->get();

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$action = '<div class="text-end">';
					if (Auth::guard('admin')->user()->can('Role.Delete')) {
						$action .= '<a class="btn btn-default border-300 btn-sm btn-delete-role me-1 text-600" data-role_id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><span class="far fa-trash-alt text-danger"></span></a>';
					}
					if (Auth::guard('admin')->user()->can('Role.Edit')) {
						$action .= '<a class="btn btn-default border-300 btn-sm btn-edit-role me-1 text-600 shadow-none" data-role_id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><span class="far fa-edit text-warning"></span></a>';
					}
					$action .= '</div>';

					$data[] = [
						$row->name,
						$action,
					];
					$srno++;
				}

				$dataCount = Role::where(function ($query) use ($search) {
					$query->where('name', 'LIKE', '%' . $search . '%');
				})->count();
			}

			return response()->json([
				"draw" => intval($r->draw),
				"recordsTotal" => $dataCount,
				"recordsFiltered" => $dataCount,
				"data" => $data,
			], 200);

		} catch (\Exception $exception) {

			LogHelper::logError(
				'exception',
				'An error occurred while fetching the role list.',
				$exception->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				"message" => "An error occurred while fetching the role list.",
			], 500);
		}
	}


	/**
	 * Create and store a new role
	 *
	 * Handles role creation with validation and logging:
	 * - Validates role name format and uniqueness
	 * - Creates role with admin guard
	 * - Returns JSON response with success/error status
	 *
	 * @param Request $r The incoming request with role data
	 * @return \Illuminate\Http\JsonResponse
	 */

	public function store(Request $r)
	{
		try {
			$rules = [
				'name' => [
					'required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:50',
					Rule::unique('roles'),
				],
			];

			$messages = [
				'name.required' => 'The name is required',
				'name.regex' => 'The name field should only contain alphabetic characters and spaces.',
				'name.max' => 'Maximum limit reached of 50 characters',
				'name.min' => 'Minimum 2 characters are required',
				'name.unique' => 'The name already exists.',
			];

			$validator = Validator::make($r->all(), $rules, $messages);

			if ($validator->fails()) {
				return response()->json(['errors' => $validator->errors()], 200);
			}

			$data = [
				'name' => ucfirst($r->name),
				'guard_name' => 'admin',
			];

			// Create role
			$result = Role::create($data);

			LogHelper::logSuccess(
				'success',
				'New role added successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

		    return response()->json([
				'status' => 200,
				'msg' => "Record added successfully.",
				'data' => $data,
			], 200);

		} catch (\Exception $ex) {

			LogHelper::logError(
				'exception',
				'An error occurred while saving the role.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

			return response()->json([
				'status' => 500,
				'msg' => 'An error occurred while saving the role.',
			], 500);
		}
	}

    /**
	 * Fetch role data for editing
	 *
	 * Retrieves role details by ID for editing purposes
	 * Includes success/error logging and appropriate JSON responses
	 *
	 * @param Request $r The incoming request containing role ID
	 * @return \Illuminate\Http\JsonResponse
	 */

    public function edit(Request $r)
	{
		try {
			$id = $r->id;
			$role = Role::find($id);

			if ($role) {

				LogHelper::logSuccess(
					'success',
					'Role data fetched successfully.',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					request()->path(),
					auth()->id()
				);

				return response()->json([
					"status" => 200,
					"msg" => "Data found",
					"data" => $role
				], 200);
			}

			return response()->json([
				"msg" => "Data Not Found"
			], 400);

		} catch (\Exception $ex) {

			LogHelper::logError(
				'exception',
				'An error occurred while fetching role.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

			return response()->json([
				"msg" => "Something went wrong while fetching role data."
			], 500);
		}
	}

	/**
	 * Update existing role information
	 *
	 * Handles role updates with validation:
	 * - Validates role name format and uniqueness (excluding current role)
	 * - Updates role information if validation passes
	 * - Includes comprehensive logging for all scenarios
	 *
	 * @param Request $r The incoming request with updated role data
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $r)
	{
		try {
			$id = $r->id;
			$rules = array(
				'id' => 'required',
				'name' => [
					'required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:50',
					Rule::unique('roles')->where(function ($query) use ($id) {
						return $query->where('id', '!=', $id);
					})
				],
			);
			$messages = array(
				'id.required' => 'Missing Id',
				'name.required' => 'The name is required',
				'name.regex' => 'The name field should only contain alphabetic characters and spaces.',
				'name.max' => 'Maximum limit reached of 50 characters',
				'name.min' => 'Minimum 2 characters are required',
				'name.unique' => 'The name is already exist.',
			);

			$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json(['errors' => $validator->errors()], 200);
			} else {
				$data = array(
					'name' => ucfirst($r->name),
				);

				$role = Role::find($id);
				$role->update($data);


				LogHelper::logSuccess(
					'success',
					'Role updated successfully.',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					request()->path(),
					auth()->id()
				);

				return response()->json([
					'status' => 200,
					'msg' => "Record updated successfully."
				], 200);
			}

		} catch (\Exception $ex) {

			LogHelper::logError(
				'exception',
				'An error occurred while updating the role.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

			return response()->json([
				'status' => 300,
				'msg' => "Something went wrong."
			], 200);
		}
	}


    /**
	 * Delete a role from the system
	 *
	 * Handles role deletion with safety checks:
	 * - Verifies role exists
	 * - Checks if role is assigned to any users (prevent deletion if in use)
	 * - Performs soft delete if safe
	 * - Includes comprehensive logging
	 *
	 * @param int $id The ID of the role to delete
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		try {
			$role = Role::find($id);
			$users = User::where("role_id", $id)
                     ->whereNull('deleted_at')
                     ->count();

			if ($users > 0) {
				return response()->json([
					'status' => 400,
					'message' => 'Unable to delete records. This role is currently assigned to one or more users.'
				]);
			}

			$role->delete();

			LogHelper::logSuccess(
				'success',
				'Role deleted successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

			return response()->json([
				'status' => 200,
				'message' => 'Record deleted successfully.'
			]);

		} catch (\Exception $ex) {

			LogHelper::logError(
				'exception',
				'An error occurred while deleting the role.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				auth()->id()
			);

			return response()->json([
				'status' => 500,
				'message' => 'An error occurred while deleting the role.'
			]);
		}
	}

}
