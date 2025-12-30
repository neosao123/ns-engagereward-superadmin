<?php
/** --------------------------------------------------------------------------------
 * This controller manages all the social media app operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\SocialMediaApp;
use DB;

class SocialMediaAppController extends Controller
{

	public function __construct()
    {
        // List & index
        $this->middleware('permission:Social Platform.List,admin')->only(['index']);
		$this->middleware('permission:Social Platform.Create,admin')->only(['create', 'store']);
		$this->middleware('permission:Social Platform.View,admin')->only('show');
		$this->middleware('permission:Social Platform.Edit,admin')->only(['edit', 'update']);
		$this->middleware('permission:Social Platform.Delete,admin')->only('destroy');

	}

	 /**
	 * Display the social platform management index page
	 *
	 * Shows the main social platform configuration view and handles:
	 * - Successful page loading with logging
	 * - Error cases with appropriate logging and user feedback
	 *
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
    public function index()
	{
		try {
			// Success log
			LogHelper::logSuccess(
				'success',
				'social platform index page loaded successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return view('main.social-app.index');
		} catch (\Exception $exception) {
			// Error log
			LogHelper::logError(
				'exception',
				'An error occurred while the social platform index page',
				$exception->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return redirect()->back()->with('error', 'An error occurred while opening the social platform index page');
		}
	}

	/**
	 * Fetch and display paginated list of social media platforms for DataTables
	 *
	 * Handles server-side processing for social platform management table with:
	 * - Search functionality
	 * - Pagination
	 * - Dynamic action buttons based on user permissions
	 * - Status formatting
	 * - Logo image handling
	 * - Comprehensive logging
	 *
	 * @param Request $request The incoming DataTables request
	 * @return \Illuminate\Http\JsonResponse
	 */

	public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$srno = $offset + 1;
			$data = [];

			$filteredData = SocialMediaApp::filterData($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];

			$canViewAction = Auth::guard('admin')->user()->canany([
				'Social Platform.Edit',
				'Social Platform.Delete',
				'Social Platform.View'
			]);

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');
					$action = '';

					// Build action dropdown if user has permission
					if ($canViewAction) {
						$action = '
						<span>
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="social-app-dropdown-' . $row->id . '" data-bs-toggle="dropdown"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="social-app-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						/*if (Auth::guard('admin')->user()->can('Social Platform.Edit')) {
							$action .= '<a class="dropdown-item text-warning" href="' . url('social-media-apps/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}*/
						if (Auth::guard('admin')->user()->can('Social Platform.View')) {
							$action .= '<a class="dropdown-item" href="' . url('social-media-apps/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						/*if (Auth::guard('admin')->user()->can('Social Platform.Delete')) {
							$action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}*/
						$action .= '</div></div></div></span>';
					}

						// Build logo image tag using storage-bucket path
					$logoPath = $row->app_logo
						? url('storage-bucket?path=' . $row->app_logo)
						: asset('assets/no-image.png'); // Fallback image if app_logo is null

					$logoImg = '<img src="' . $logoPath . '" alt="App Logo" style="width: 40px; height: 40px; object-fit: contain; border-radius: 5px;" />';

					// Format status
					$status = $row->is_active == 1
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';

					// Build row data
					$rowData = [];

					if ($canViewAction) {
						$rowData[] = $action;
					}

					$rowData[] = $row->app_name;
					$rowData[]=$logoImg;
					$rowData[] = $status;
					$rowData[] = $formattedDate;

					$data[] = $rowData;
					$srno++;
				}
			}

			// Log success
			LogHelper::logSuccess(
				'success',
				'Social media app list fetched successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				$request->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				"draw" => intval($request->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data,
				"result" => $result
			], 200);
		} catch (\Exception $ex) {
			// Log the error
			LogHelper::logError(
				'exception',
				'An error occurred while fetching the social platform list',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				$request->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				"message" => "An error occurred while fetching the social platform list",
			], 500);
		}
	}



	/**
	 * Display the social platform creation form
	 *
	 * Renders the view for adding a new social media platform with:
	 * - Success logging when view loads properly
	 * - Error handling and logging for any exceptions
	 * - Appropriate user feedback
	 *
	 * @param Request $r The incoming request
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function create(Request $r)
	{
		try {
			// Log success for rendering the create view
			LogHelper::logSuccess(
				'success',
				'social platform app add page loaded successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return view('main.social-app.add');

		} catch (\Exception $ex) {
			// Log the error
			LogHelper::logError(
				'exception',
				'An error occurred while creating the social platform app',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			// Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the social platform add.');
		}
	}


	/**
	 * Store a newly created social media app in storage
	 *
	 * Handles the creation of a new social media platform with:
	 * - Input validation (name uniqueness, logo requirements)
	 * - File upload handling for app logo
	 * - Database transaction management
	 * - Comprehensive success/error logging
	 * - JSON response for AJAX requests
	 *
	 * @param Request $request The incoming request with form data
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		DB::beginTransaction(); // Start the transaction
		try {
			// Validation rules
			$rules = [
				'app_name' => [
					'required',
					'string',
					'max:255',
					Rule::unique('social_media_apps', 'app_name')->whereNull('deleted_at'),
				],
				'app_logo' => 'required|image|mimes:jpeg,jpg,png|dimensions:width=512,height=512',
			];

			// Custom error messages
			$messages = [
				'app_name.required' => 'The social platform name field is required.',
				'app_name.unique' => 'The social platform name has already been taken.',
				'app_name.max' => 'The social platform name cannot be more than 255 characters.',
				'app_logo.required' => 'The social platform logo is required.',
				'app_logo.image' => 'The social platform logo must be an image.',
				'app_logo.mimes' => 'The social platform logo must be a file of type: jpeg, jpg, png.',
				'app_logo.dimensions' => 'The social platform logo must be exactly 512x512 pixels in size.',
			];

			// Validate
			$validator = Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'errors' => $validator->errors()
				], 200);
			}

			// Save data
			$app = new SocialMediaApp;
			$app->app_name = $request->app_name;
			$app->is_active = $request->is_active ? 1 : 0;

			// Save app logo
			if ($request->hasFile('app_logo')) {
				$file = $request->file('app_logo');
				$imageName = 'app-logo-' . time() . '.' . $file->getClientOriginalExtension();
				$path = $file->storeAs('app-logo', $imageName, 'public');
				$app->app_logo = $path;
			}

			$app->save();

			DB::commit(); // Commit the transaction

			LogHelper::logSuccess(
				'success',
				'social platform app was added successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$app->id
			);

			return response()->json([
				'status' => 200,
				'message' => 'social platform app added successfully.'
			], 200);

		} catch (\Exception $ex) {
			DB::rollback(); // Rollback on error

			LogHelper::logError(
				'exception',
				'An error occurred while saving the social platform app',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				'status' => 'error',
				'message' => 'An error occurred while saving the social platform app.'
			], 500);
		}
	}


	/**
	 * Display the form for editing a social media platform
	 *
	 * Retrieves and displays the specified social media app for editing with:
	 * - Record validation (checks if exists and not soft-deleted)
	 * - Comprehensive logging for both success and error cases
	 * - Proper error handling and user feedback
	 *
	 * @param string $id The ID of the social media app to edit
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function edit(string $id)
	{
		try {
			// Fetch the app record
			$app = SocialMediaApp::where('id', $id)->whereNull('deleted_at')->first();

			if (!$app) {
				// Log the error for invalid ID
				LogHelper::logError(
					'error',
					'Invalid social platform App ID provided for editing',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					request()->path(),
					$id
				);

				return redirect()->back()->with('error', 'Invalid social platform app.');
			}

			// Log success - app found for editing
			LogHelper::logSuccess(
				'success',
				'social platform fetched for editing successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$id
			);

			return view('main.social-app.edit', compact('app'));

		} catch (\Exception $ex) {
			// Log the exception
			LogHelper::logError(
				'exception',
				'An exception occurred while editing the social platform',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$id
			);

			return redirect()->back()->with('error', 'An error occurred while editing the social platform.');
		}
	}


	/**
	 * Update the specified social media app resource in storage.
	 *
	 * @param Request $request The incoming HTTP request containing update data
	 * @param string $id The ID of the social media app to update
	 * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure
	 */

	public function update(Request $request, string $id)
	{
		DB::beginTransaction();
		try {
			// Validation rule for logo depending on whether an old logo exists
			$app_logo = ($request->previous_app_logo ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png|dimensions:width=512,height=512';

			// Validation Rules
			$rules = [
				'id' => 'required',
				'app_name' => [
					'required',
					'string',
					'max:255',
					Rule::unique('social_media_apps', 'app_name')->whereNull('deleted_at')
						->ignore($id)
				],
				'app_logo' => $app_logo,
			];

			// Custom Error Messages
			$messages = [
				'id.required' => 'The ID field is required.',
				'app_name.required' => 'The social platform name is required.',
				'app_name.string' => 'The social platform name must be a string.',
				'app_name.max' => 'The social platform name may not be greater than 255 characters.',
				'app_name.unique' => 'The social platform name has already been taken.',
				'app_logo.image' => 'The social platform logo must be an image.',
				'app_logo.mimes' => 'The social platform logo must be a file of type: jpeg, jpg, png.',
				'app_logo.dimensions' => 'The social platform logo must be exactly 512x512 pixels.',
			];

			$validator = Validator::make($request->all(), $rules, $messages);

			// Handle Validation Failure
			if ($validator->fails()) {
				LogHelper::logError(
					'validation_error',
					'Validation failed while updating social media app',
					json_encode($validator->errors()->all()),
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					request()->path(),
					$id
				);

				return response()->json([
					'status' => 'error',
					'errors' => $validator->errors()
				], 200);
			}

			// Retrieve record
			$app = SocialMediaApp::findOrFail($id);
			$app->app_name = $request->app_name;
			$app->is_active = $request->has('is_active') ? 1 : 0;

			// Handle new logo upload
			if ($request->hasFile('app_logo')) {
				$file = $request->file('app_logo');
				$imageName = 'app-logo-' . time() . '.' . $file->getClientOriginalExtension();
				$path = Storage::disk('public')->putFileAs('app-logo', $file, $imageName);
				$app->app_logo = $path;
			}

			$app->save();
			DB::commit();

			// Log success
			LogHelper::logSuccess(
				'success',
				'social platform updated successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$app->id
			);

			return response()->json([
				'status' => 200,
				'message' => 'social platform updated successfully.',
				'data' => $app
			], 200);

		} catch (\Exception $ex) {
			DB::rollBack();

			LogHelper::logError(
				'exception',
				'Failed to update social platform.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$id
			);

			return response()->json([
				'status' => 'error',
				'message' => 'An error occurred while updating the app.',
				'error' => $ex->getMessage()
			], 500);
		}
	}


	/**
	 * Soft delete a social media app by marking it as inactive and setting deleted_at timestamp.
	 * This follows Laravel's convention for soft deletes while maintaining referential integrity.
	 *
	 * @param string $id The ID of the social media app to soft delete
	 * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure
	 */
	public function destroy(string $id)
	{
		try {
			// Find the social media app by ID
			$app = SocialMediaApp::find($id);

			// Check if the app exists
			if (!$app) {
				// Log error for not found
				LogHelper::logError(
					'not_found',
					'social platform not found',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				// Return error response
				return response()->json([
					'success' => false,
					'error' => 'social platform not found.'
				]);
			}

			// Soft delete: set is_active to 0 and deleted_at timestamp
			$app->is_active = 0;
			$app->deleted_at = now();
			$app->save();

			// Log success
			LogHelper::logSuccess(
				'success',
				'social platform deleted successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			// Return success response
			return response()->json(['success' => true]);
		} catch (\Exception $ex) {
			// Log the exception error
			LogHelper::logError(
				'exception',
				'An error occurred while deleting the social platform',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			// Return error response
			return response()->json([
				'message' => 'An error occurred while deleting the social platform.',
			], 500);
		}
	}

	/**
	 * Remove the specified app logo from storage and clear the reference from database.
	 *
	 * This performs two main operations:
	 * 1. Physically deletes the logo file from storage if it exists
	 * 2. Clears the logo reference from the database record
	 *
	 * @param int $id The ID of the social media app
	 * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure
	 */
	public function delete_logo($id)
	{
		try {
			// Find the social media app by ID
			$app = SocialMediaApp::find($id);

			if ($app && $app->app_logo) {
				// Check if the logo file exists on disk and delete it
				if (Storage::disk('public')->exists($app->app_logo)) {
					Storage::disk('public')->delete($app->app_logo);
				}
				// Clear the app_logo field
				$app->app_logo = null;
				$app->save();

				LogHelper::logSuccess(
					'The app logo deleted successfully',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				return response()->json(['success' => true, 'message' => 'social platform logo deleted successfully.']);
			}

			LogHelper::logError(
				'not_found',
				'App or logo not found',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return response()->json(['success' => false, 'message' => 'social platform logo not found.'], 404);

		} catch (\Exception $ex) {
			LogHelper::logError(
				'exception',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return response()->json(['success' => false, 'message' => 'An error occurred while deleting the app logo'], 500);
		}
	}


	/**
	 * Display the specified social media app.
	 *
	 * Only shows apps that haven't been soft deleted (where deleted_at is null).
	 * Returns a view with the app data or redirects back with error if not found.
	 *
	 * @param string $id The ID of the social media app to show
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function show(string $id)
	{
		try {
			// Fetch the app record
			$app = SocialMediaApp::where('id', $id)->whereNull('deleted_at')->first();

			if (!$app) {
				// Log the error with app ID
				LogHelper::logError(
					'not_found',
					'Invalid social platform ID',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				return redirect()->back()->with('error', 'Invalid social platform.');
			}

			return view('main.social-app.show', compact('app'));

		} catch (\Exception $ex) {
			// Log the exception including app ID
			LogHelper::logError(
				'exception',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return redirect()->back()->with('error', 'An error occurred while showing the social platform.');
		}
	}





}
