<?php

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
use App\Models\Setting;
use DB;

class SettingController extends Controller
{
	
	//author:neosao
	//date:20-6-2025
	public function __construct()
    {
        // List & index
        $this->middleware('permission:Setting.List,admin')->only(['index']);
		$this->middleware('permission:Setting.Create,admin')->only(['create', 'store']);
		$this->middleware('permission:Setting.View,admin')->only('show');
		$this->middleware('permission:Setting.Edit,admin')->only(['edit', 'update']);
		$this->middleware('permission:Setting.Delete,admin')->only('destroy');

	}

	 /**
     * Display a index page of the resource.
     */
    public function index()
	{
		try {
			// Success log
			LogHelper::logSuccess(
				'success',
				'setting index page loaded successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return view('main.setting.index');
		} catch (\Exception $exception) {
			// Error log
			LogHelper::logError(
				'exception',
				'An error occurred while the setting index page',
				$exception->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return redirect()->back()->with('error', 'An error occurred while opening the setting index page');
		}
	}
	
	
	 /*
    list of setting
    */
   
	public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$srno = $offset + 1;
			$data = [];

			$filteredData = Setting::filterData($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];

			$canViewAction = Auth::guard('admin')->user()->canany([
				'Setting.Edit',
				'Setting.Delete',
				'Setting.View'
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
						if (Auth::guard('admin')->user()->can('Setting.Edit')) {
							$action .= '<a class="dropdown-item text-warning" href="' . url('setting/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if (Auth::guard('admin')->user()->can('Setting.View')) {
							$action .= '<a class="dropdown-item" href="' . url('setting/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						if (Auth::guard('admin')->user()->can('Setting.Delete')) {
							$action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}
						$action .= '</div></div></div></span>';
					}
					
					$logoImg = '';

					if (!empty($row->logo_image) && Storage::disk('public')->exists($row->logo_image)) {
						$logoPath = url('storage-bucket?path=' . $row->logo_image);
						$logoImg = '<img src="' . $logoPath . '" alt="Logo" style="width: 40px; height: 40px; object-fit: contain; border-radius: 5px;" />';
					}

					// Format status
					$status = $row->is_active == 1
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';

					// Build row data
					$rowData = [];

					if ($canViewAction) {
						$rowData[] = $action;
					}

					$rowData[] = $row->contact_email;
					$rowData[] = $row->contact_phone;
					$rowData[] = $row->contact_email;
					$rowData[] = $row->support_contact;					
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
				'Setting list fetched successfully.',
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
				'An error occurred while fetching the setting list',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				$request->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				"message" => "An error occurred while fetching the setting list",
			], 500);
		}
	}
	
	//create setting
	public function create(Request $request)
	{
		try {
			// Log when settings page is loaded
			LogHelper::logSuccess(
				'success',
				'Setting create page loaded successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return view('main.setting.add'); // Make sure this view exists

		} catch (\Exception $ex) {
			LogHelper::logError(
				'exception',
				'An error occurred while loading the setting create page.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return redirect()->back()->with('error', 'An error occurred while loading the create page.');
		}
	}
	
	//setting 
	public function store(Request $request)
	{
		DB::beginTransaction();
		try {
			// Validation rules
			$rules = [
				'contact_email'   => 'required|email|max:150',
				'contact_phone'   => 'nullable|digits_between:10,15',
				'support_email'   => 'nullable|email|max:150',
				'support_contact' => 'nullable|digits_between:10,15',
				'logo_image'      => 'required|image|mimes:jpeg,jpg,png|dimensions:width=512,height=512',
			];

			// Custom error messages
			$messages = [
				'contact_email.required'   => 'Contact email is required.',
				'contact_email.email'      => 'Enter a valid contact email.',
				'contact_email.max'        => 'Contact email cannot exceed 150 characters.',
				'contact_phone.digits_between' => 'Contact phone must be 10 to 15 digits.',
				'support_email.email'      => 'Enter a valid support email.',
				'support_email.max'        => 'Support email cannot exceed 150 characters.',
				'support_contact.digits_between' => 'Support contact must be 10 to 15 digits.',
				'logo_image.required'      => 'Logo image is required.',
				'logo_image.image'         => 'Logo must be an image.',
				'logo_image.mimes'         => 'Logo must be a file of type: jpeg, jpg, png.',
				'logo_image.dimensions'    => 'Logo must be exactly 512x512 pixels.',
			];

			// Validate request
			$validator = Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'errors' => $validator->errors()
				], 200);
			}

			// Save data
			$setting = new Setting();
			$setting->contact_email   = $request->contact_email;
			$setting->contact_phone   = $request->contact_phone;
			$setting->support_email   = $request->support_email;
			$setting->support_contact = $request->support_contact;
			$setting->is_active       = $request->has('is_active') ? 1 : 0;

			// Save logo_image
			if ($request->hasFile('logo_image')) {
				$file = $request->file('logo_image');
				$filename = 'setting-logo-' . time() . '.' . $file->getClientOriginalExtension();
				$path = $file->storeAs('setting-logos', $filename, 'public');
				$setting->logo_image = $path;
			}

			$setting->save();
			DB::commit();

			LogHelper::logSuccess(
				'success',
				'Setting created successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$setting->id
			);

			return response()->json([
				'status' => 200,
				'message' => 'Setting saved successfully.'
			], 200);

		} catch (\Exception $ex) {
			DB::rollback();

			LogHelper::logError(
				'exception',
				'An error occurred while saving the setting.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json([
				'status' => 'error',
				'message' => 'An error occurred while saving the setting.'
			], 500);
		}
	}
	
	
	 /**
     * Show the form for editing the specified setting.
     */
    public function edit(string $id)
    {
        try {
            // Fetch the setting record
            $setting = Setting::where('id', $id)->whereNull('deleted_at')->first();

            if (!$setting) {
                LogHelper::logError(
                    'error',
                    'Invalid setting ID provided for editing',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    $id
                );

                return redirect()->back()->with('error', 'Invalid setting.');
            }

            LogHelper::logSuccess(
                'success',
                'Setting fetched for editing successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return view('main.setting.edit', compact('setting'));

        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An exception occurred while editing the setting',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return redirect()->back()->with('error', 'An error occurred while editing the setting.');
        }
    }
	
	
	/**
     * Update the specified setting in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            // Determine if logo_image is required or nullable (if existing logo present)
            $logo_rule = ($request->previous_logo_image ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png|dimensions:width=512,height=512';

            // Validation rules
            $rules = [
                'contact_email' => ['required', 'email', 'max:255'],
                'support_email' => ['nullable', 'email', 'max:255'],
                'contact_phone' => ['required', 'digits_between:10,15'],
                'support_contact' => ['nullable', 'digits_between:10,15'],
                'logo_image' => $logo_rule,
            ];

            // Custom error messages (optional)
            $messages = [
                'contact_email.required' => 'The contact email is required.',
                'contact_email.email' => 'Please enter a valid contact email.',
                
                'support_email.email' => 'Please enter a valid support email.',
                'contact_phone.required' => 'The contact phone is required.',
                'contact_phone.digits_between' => 'Enter valid contact number.',
                
                'support_contact.digits_between' => 'Enter valid support contact number',
                'logo_image.image' => 'The logo image must be an image file.',
                'logo_image.mimes' => 'The logo image must be a file of type: jpeg, jpg, png.',
                'logo_image.dimensions' => 'The logo image must be exactly 512x512 pixels.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                LogHelper::logError(
                    'validation_error',
                    'Validation failed while updating setting',
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

            $setting = Setting::findOrFail($id);

            $setting->contact_email = $request->contact_email;
            $setting->support_email = $request->support_email;
            $setting->contact_phone = $request->contact_phone;
            $setting->support_contact = $request->support_contact;
            $setting->is_active       = $request->has('is_active') ? 1 : 0;
            // Handle logo upload
			if ($request->hasFile('logo_image')) {
				$file = $request->file('logo_image');
				$filename = 'setting-logo-' . time() . '.' . $file->getClientOriginalExtension();
				$path = $file->storeAs('setting-logos', $filename, 'public');
				$setting->logo_image = $path;
			}

            $setting->save();

            DB::commit();

            LogHelper::logSuccess(
                'success',
                'Setting updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $setting->id
            );

            return response()->json([
                'status' => 200,
                'message' => 'Setting updated successfully.',
                'data' => $setting
            ], 200);

        } catch (\Exception $ex) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Failed to update setting.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the setting.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
	
	
	/**
	 * Soft delete the specified setting.
	 */
	public function destroy(string $id)
	{
		try {
			$setting = Setting::find($id);

			if (!$setting) {
				LogHelper::logError(
					'not_found',
					'Setting not found',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				return response()->json([
					'success' => false,
					'error' => 'Setting not found.'
				]);
			}

			// Soft delete: mark as inactive and set deleted_at timestamp
			$setting->is_active = 0;
			$setting->deleted_at = now();
			$setting->save();

			LogHelper::logSuccess(
				'success',
				'Setting deleted successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return response()->json(['success' => true]);
		} catch (\Exception $ex) {
			LogHelper::logError(
				'exception',
				'An error occurred while deleting the setting',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return response()->json([
				'message' => 'An error occurred while deleting the setting.',
			], 500);
		}
	}

	/**
	 * Remove the specified logo image from storage.
	 */
	public function delete_logo(string $id)
	{
		try {
			$setting = Setting::find($id);

			if ($setting && $setting->logo_image) {
				if (Storage::disk('public')->exists($setting->logo_image)) {
					Storage::disk('public')->delete($setting->logo_image);
				}

				$setting->logo_image = null;
				$setting->save();

				LogHelper::logSuccess(
					'success',
					'Setting logo deleted successfully.',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				return response()->json(['success' => true, 'message' => 'Setting logo deleted successfully.']);
			}

			LogHelper::logError(
				'not_found',
				'Setting or logo not found',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$id
			);

			return response()->json(['success' => false, 'message' => 'Setting logo not found.'], 404);

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

			return response()->json(['success' => false, 'message' => 'An error occurred while deleting the logo'], 500);
		}
	}

	/**
	 * Display the specified setting.
	 */
	public function show(string $id)
	{
		try {
			$setting = Setting::where('id', $id)->whereNull('deleted_at')->first();

			if (!$setting) {
				LogHelper::logError(
					'not_found',
					'Invalid setting ID',
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$id
				);

				return redirect()->back()->with('error', 'Invalid setting.');
			}

			return view('main.setting.show', compact('setting'));

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

			return redirect()->back()->with('error', 'An error occurred while showing the setting.');
		}
	}




}