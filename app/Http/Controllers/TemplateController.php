<?php

/** --------------------------------------------------------------------------------
 * This controller manages all the Template operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            if (!$this->user) {
                return $next($request);
            }

            $role_id = $this->user->role_id;
            $action = $request->route()->getActionMethod();

            $permissions = [
                'index' => 'Template.List',
                'list' => 'Template.List',
                'create' => 'Template.Create',
                'store' => 'Template.Create',
                'show' => 'Template.View',
                'edit' => 'Template.Edit',
                'update' => 'Template.Edit',
                'destroy' => 'Template.Delete',
                'preview' => 'Template.View',
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
     * Display a listing of templates
     */
    public function index()
    {
        try {
            LogHelper::logSuccess(
                'success',
                'Template index page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.template.index');
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while loading the template index page',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while loading the template list.');
        }
    }

    /**
     * Get a paginated and filtered list of templates for DataTables
     */
    public function list(Request $r)
    {
        try {
            $limit = $r->length;
            $offset = $r->start;
            $search = $r->input('search.value') ?? "";

            $query = Template::where('is_delete', 0);

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('subtitle', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $records = $query->orderBy('id', 'DESC')->offset($offset)->limit($limit)->get();

            $data = [];
            $adminUser = Auth::guard('admin')->user();
            if (!$adminUser) {
                return response()->json(["message" => "Unauthorized"], 401);
            }
            $role_id = $adminUser->role_id;
            
            $canViewAction = isRolePermission($role_id, 'Template.Edit') || 
                             isRolePermission($role_id, 'Template.View');

            if ($records->count() > 0) {
                foreach ($records as $row) {
                    $status = $row->is_active == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">In-Active</span></div>';

                    $action = '';
                    if ($canViewAction) {
                        $action .= '
                            <span>
                                <div class="dropdown font-sans-serif position-static">
                                    <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="template-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
                                        aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="template-dropdown-' . $row->id . '">
                                        <div class="bg-white py-2">';

                        if (isRolePermission($role_id, 'Template.View')) {
                            $action .= '<a class="dropdown-item" href="' . url('templates/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
                        }
                        if (isRolePermission($role_id, 'Template.Edit')) {
                            $action .= '<a class="dropdown-item" href="' . url('templates/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> Edit</a>';
                        }

                        $action .= '</div></div></div></span>';
                    }

                    $rowData = [];
                    if ($canViewAction) {
                        $rowData[] = $action;
                    }
                    $rowData[] = $row->title;
                    $rowData[] = $status;
                    $rowData[] = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');

                    $data[] = $rowData;
                }
            }

            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the template list',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while fetching the template list",
            ], 500);
        }
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        try {
            return view('main.template.add');
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'An error occurred while loading the add page.');
        }
    }

    /**
     * Store a newly created template in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'subtitle' => 'nullable|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 200);
        }

        try {
            DB::beginTransaction();

            $template = new Template();
            $template->title = $request->title;
            $template->subtitle = $request->subtitle;
            $template->description = $request->description;
            $template->is_active = $request->is_active ?? 1;
            $template->save();

            DB::commit();

            LogHelper::logSuccess(
                'success',
                'Template added successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $template->id
            );

            return response()->json([
                'status' => 200,
                'message' => 'Template added successfully.'
            ], 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified template
     */
    public function show($id)
    {
        try {
            $template = Template::findOrFail($id);
            return view('main.template.view', compact('template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'Template not found.');
        }
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit($id)
    {
        try {
            $template = Template::findOrFail($id);
            return view('main.template.edit', compact('template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'Template not found.');
        }
    }

    /**
     * Update the specified template in storage
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'subtitle' => 'nullable|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 200);
        }

        try {
            DB::beginTransaction();

            $template = Template::findOrFail($id);
            $template->title = $request->title;
            $template->subtitle = $request->subtitle;
            $template->description = $request->description;
            $template->is_active = $request->is_active ?? 1;
            $template->save();

            DB::commit();

            LogHelper::logSuccess(
                'success',
                'Template updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $template->id
            );

            return response()->json([
                'status' => 200,
                'message' => 'Template updated successfully.'
            ], 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified template from storage (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $template = Template::findOrFail($id);
            $template->is_delete = 1;
            $template->save();

            LogHelper::logSuccess(
                'success',
                'Template deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $id
            );

            return response()->json([
                'status' => 200,
                'message' => 'Template deleted successfully.'
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Preview the template
     */
    public function preview(Request $request)
    {
        $content = $request->description;
        
        return view('main.template.preview', compact('content'));
    }
}
