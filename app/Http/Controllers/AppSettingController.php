<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Setting;
use DB;

class AppSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:AppSetting.Edit,admin');
    }

    /**
     * Display a index page of the resource.
     */
    public function index()
    {
        try {
            LogHelper::logSuccess(
                'success',
                'App setting index page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.app-setting.index');
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while the app setting index page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while opening the app setting index page');
        }
    }

    /*
    list of app setting
    */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search.value') ?? "";
            $limit = $request->length;
            $offset = $request->start;
            $srno = $offset + 1;
            $data = [];

            // We only want settings that have a setting_name (android/ios)
            $query = Setting::whereNotNull('setting_name')
                           ->whereNull('deleted_at');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('setting_name', 'like', '%' . $search . '%')
                      ->orWhere('setting_value', 'like', '%' . $search . '%');
                });
            }

            $totalRecords = $query->count();
            
            if ($limit != -1) {
                $query->offset($offset)->limit($limit);
            }
            
            $result = $query->orderBy('id', 'desc')->get();

            $canViewAction = Auth::guard('admin')->user()->can('AppSetting.Edit');

            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');
                    $action = '';

                    // Build action dropdown if user has permission
                    if ($canViewAction) {
                        $action = '
                        <span>
                            <div class="dropdown font-sans-serif position-static">
                                <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="app-setting-dropdown-' . $row->id . '" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="app-setting-dropdown-' . $row->id . '">
                                    <div class="bg-white py-2">';
                        
                        $action .= '<a class="dropdown-item text-warning" href="' . url('app-settings/edit/' . $row->id) . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
                        
                        $action .= '</div></div></div></span>';
                    }

                    // Format update compulsory
                    $updateCompulsory = $row->is_update_compulsory == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Yes</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">No</span></div>';

                    // Format status
                    $status = $row->is_active == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';

                    // Build row data
                    $rowData = [];

                    if ($canViewAction) {
                        $rowData[] = $action;
                    }

                    $rowData[] = ucfirst($row->setting_name);
                    $rowData[] = $row->setting_value;
                    $rowData[] = $updateCompulsory;
                    $rowData[] = $status;
                    $rowData[] = $formattedDate;

                    $data[] = $rowData;
                    $srno++;
                }
            }

            return response()->json([
                "draw" => intval($request->draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords,
                "data" => $data,
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the app setting list',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $request->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while fetching the app setting list",
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit(string $id)
    {
        try {
            $setting = Setting::where('id', $id)->whereNotNull('setting_name')->whereNull('deleted_at')->first();

            if (!$setting) {
                return redirect()->back()->with('error', 'Invalid app setting.');
            }

            LogHelper::logSuccess(
                'success',
                'App setting fetched for editing successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return view('main.app-setting.edit', compact('setting'));

        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An exception occurred while editing the app setting',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return redirect()->back()->with('error', 'An error occurred while editing the app setting.');
        }
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'setting_value' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200);
            }

            $setting = Setting::findOrFail($id);
            $setting->setting_value = $request->setting_value;
            $setting->is_update_compulsory = $request->has('is_update_compulsory') ? 1 : 0;
            $setting->is_active = $request->has('is_active') ? 1 : 0;
            $setting->save();

            DB::commit();

            LogHelper::logSuccess(
                'success',
                'App setting updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $setting->id
            );

            return response()->json([
                'status' => 200,
                'message' => 'App setting updated successfully.'
            ], 200);

        } catch (\Exception $ex) {
            DB::rollBack();
            LogHelper::logError(
                'exception',
                'Failed to update app setting.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the app setting.',
            ], 500);
        }
    }
}
