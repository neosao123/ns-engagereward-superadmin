<?php

/**
 * --------------------------------------------------------------------------------
 * This controller manages all Subscription Plan operations
 *
 * Features:
 * - List, create, edit, view, and delete subscription plans
 * - Integration with Social Media Apps for plan mapping
 * - Handles server-side pagination for DataTables
 * - Full logging of success and error cases
 *
 * @author
 *----------------------------------------------------------------------------------
 */

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\SocialMediaApp;

use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Helpers\LogHelper;
use App\Models\SubscriptionPlanSocialMedia;
use App\Models\SubscriptionPurchase;

class SubscriptionPlanController extends Controller
{
    /**
     * Apply middleware permissions for access control.
     * Each route is restricted to specific admin permissions.
     */
    public function __construct()
    {
        $this->middleware('permission:Subscription.List,admin')->only(['index', 'list']);
        $this->middleware('permission:Subscription.Create,admin')->only(['create', 'store']);
        $this->middleware('permission:Subscription.View,admin')->only('show');
        $this->middleware('permission:Subscription.Edit,admin')->only(['edit', 'update']);
        $this->middleware('permission:Subscription.Delete,admin')->only('destroy');
    }

    /**
     * Display the subscription management index page.
     *
     * - Loads subscription plan management view
     * - Logs success/error cases
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */

    public function index()
    {
        try {
            LogHelper::logSuccess('success', 'Subscription index loaded', __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return view('main.subscription-plan.index');
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Failed to load subscription index', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return redirect()->back()->with('error', 'Failed to open subscription index');
        }
    }


    /**
     * Fetch social media apps for select2 dropdown in subscription form.
     *
     * - Supports search by app name
     * - Limits results for performance
     * - Returns JSON response
     *
     * @param Request $r
     * @return \Illuminate\Http\JsonResponse
     */

    public function social_media_app(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = SocialMediaApp::where('app_name', 'like', '%' . $search . '%')
                ->whereNull("deleted_at")
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->app_name];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching social media apps list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }

    /**
     * Fetch paginated list of subscriptions for DataTables.
     *
     * - Supports search filter
     * - Handles pagination and ordering
     * - Formats prices, discounts, and dates
     * - Builds action buttons dynamically based on permissions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function list(Request $request)
    {
        try {
            $search = $request->input('search.value');
            $limit = $request->length;
            $offset = $request->start;

            $query = Subscription::query()
                ->whereNull('deleted_at')
                ->when($search, function ($q) use ($search) {
                    $q->where('subscription_title', 'like', "%$search%");
                });

            $total = $query->count();
            $subscriptions = $query->offset($offset)->limit($limit)->orderBy("id", "DESC")->get();

            $data = [];
            foreach ($subscriptions as $sub) {
                $action = '';
                if (Auth::guard('admin')->user()->canany(['Subscription.Edit', 'Subscription.View', 'Subscription.Delete'])) {
                    $action = '
                    <div class="dropdown">
                        <button class="btn btn-link text-600 btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu">';
                    if (Auth::guard('admin')->user()->can('Subscription.Edit')) {
                        $action .= '<a class="dropdown-item text-warning" href="' . url('subscription-plan/' . $sub->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
                    }
                    if (Auth::guard('admin')->user()->can('Subscription.View')) {
                        $action .= '<a class="dropdown-item" href="' . url('subscription-plan/' . $sub->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
                    }
                    if (Auth::guard('admin')->user()->can('Subscription.Delete')) {
                        $action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $sub->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
                    }
                    $action .= '</div></div>';
                }

                $currencySymbol = ''; // default symbol

                if (!empty($sub->currency_code)) {
                    if (preg_match('/\((.*?)\)/', $sub->currency_code, $matches)) {
                        $currencySymbol = $matches[1];
                    }
                }

                // Per month price
                $perMonthPrice = $currencySymbol . number_format($sub->subscription_per_month_price, 2);

                // Total price
                $totalPrice = $currencySymbol . number_format($sub->subscription_total_price, 2);

                // Discount
                if (!empty($sub->discount_value) && strtolower($sub->discount_type) === 'flat') {
                    $discountValue = $currencySymbol . number_format($sub->discount_value, 2);
                } elseif (!empty($sub->discount_value)) {
                    $discountValue = round($sub->discount_value);
                } else {
                    $discountValue = '-';
                }

                $data[] = [
                    $action,
                    $sub->subscription_title,
                    $sub->subscription_months,
                    $perMonthPrice,
                    $sub->discount_type ?? "-",
                    $discountValue,
                    '<div style="font-size:14px;">' . $totalPrice . '</div>',
                    $sub->from_date ? Carbon::parse($sub->from_date)->format('d-m-Y') : '-',
                    $sub->to_date ? Carbon::parse($sub->to_date)->format('d-m-Y') : '-',
                    $sub->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>',
                ];
            }

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Error in subscription list', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return response()->json(['error' => 'Failed to fetch subscription list'], 500);
        }
    }

    /**
     * Show create form for a new subscription plan.
     */

    public function create()
    {
        try {
            LogHelper::logSuccess('success', 'Subscription add page loaded', __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return view('main.subscription-plan.add');
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Error loading add page', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return back()->with('error', 'Failed to open add subscription page');
        }
    }


    public function currency_list(Request $r)
    {
        try {
            $search = $r->input('search');
            $html = [];

            foreach (getCurrencyList() as $code => $label) {
                if (!$search || stripos($code, $search) !== false || stripos($label, $search) !== false) {
                    $html[] = ['id' => $label, 'text' => $label];
                }
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching currency list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([]);
        }
    }


    /**
     * Store a newly created subscription plan in DB.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $rules = [
                'subscription_title' => 'required|string|max:255',
                //'social_media'=>'required',
                'subscription_months' => [
                    'required',
                    'integer',
                    Rule::unique('subscriptions')->where(function ($query) use ($request) {
                        return $query->where('subscription_title', $request->subscription_title)
                            ->whereNull('deleted_at');
                    }),
                ],
                'subscription_per_month_price' => 'required|numeric|min:0',
                'from_date' => 'required|date',
                'discount_type' => 'nullable|in:flat,percentage',
                'currency' => 'required',

            ];

            $messages = [
                'subscription_title.required' => 'The subscription title is required.',
                'subscription_title.string' => 'The subscription title must be a string.',
                'subscription_title.max' => 'The subscription title may not be greater than 255 characters.',
                //'social_media.required'        => 'Social media is required.',
                'subscription_months.required' => 'The number of months is required.',
                'subscription_months.integer' => 'The number of months must be an integer.',
                'subscription_months.unique' => 'A subscription with this title and number of months already exists.',

                'subscription_per_month_price.required' => 'The price per month is required.',
                'subscription_per_month_price.numeric' => 'The price must be a number.',
                'subscription_per_month_price.min' => 'The price must be at least 0.',

                'from_date.required' => 'The start date is required.',
                'from_date.date' => 'The start date must be a valid date.',

                'discount_value.required' => 'The discount is required.',
                'discount_value.numeric' => 'The discount must be a number.',
                'discount_value.min' => 'The discount must be at least :min.',
                'discount_value.max' => 'The discount cannot be more than :max.',
                'currency.required' => 'Currency code is required.',
            ];



            $validator = Validator::make($request->all(), $rules, $messages);

            $validator->sometimes('discount_value', 'required|numeric|min:1|max:100', function ($input) {
                return $input->discount_type === 'percentage';
            });

            $validator->sometimes('discount_value', 'required|numeric|min:0', function ($input) {
                return $input->discount_type === 'flat';
            });

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ]);
            }

            $from = Carbon::parse($request->from_date);
            $to = Carbon::parse($request->to_date);

            $discountType = $request->discount_type;
            $discountValue = $request->discount_value;
            $total = $request->subscription_months * $request->subscription_per_month_price;

            if ($discountType === 'flat') {
                $total -= $discountValue;
            } elseif ($discountType === 'percentage') {
                $total -= ($total * $discountValue / 100);
            }

            if ($total < 0) $total = 0;

            $sub = new Subscription();
            $sub->subscription_title = $request->subscription_title;
            $sub->subscription_months = $request->subscription_months;
            $sub->subscription_per_month_price = $request->subscription_per_month_price;
            $sub->subscription_total_price = $total;
            $sub->discount_type = $discountType;
            $sub->discount_value = $discountValue;
            $sub->currency_code = $request->currency;
            $sub->from_date = $from;
            $sub->to_date = $to;
            $sub->is_active = 1;
            $sub->save();

            // Insert social media relations
            /*foreach ($request->social_media as $socialMediaId) {
				SubscriptionPlanSocialMedia::create([
					'subscription_id' => $sub->id,
					'social_media_id' => $socialMediaId
				]);
			}*/


            DB::commit();

            LogHelper::logSuccess('success', 'Subscription added', __FUNCTION__, __FILE__, __LINE__, request()->path(), $sub->id);
            return response()->json(['status' => 200, 'message' => 'Subscription added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::logError('exception', 'Error storing subscription', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), auth()->id());
            return response()->json(['status' => 'error', 'message' => 'Failed to store subscription'], 500);
        }
    }

    /**
     * Show the edit form for a specific subscription plan.
     */
    public function edit($id)
    {
        try {
            $subscription = Subscription::with('subscriptionPlanSocialMedia.socialMediaApp')->findOrFail($id);

            // Get all social media apps for selection
            $allSocialMediaApps = SocialMediaApp::where('is_active', 1)
                ->whereNull('deleted_at')
                ->get();

            // Get only the selected social media app IDs for this subscription
            $selectedSocialMediaIds = $subscription->subscriptionPlanSocialMedia
                ->pluck('social_media_id')
                ->toArray();

            LogHelper::logSuccess('success', 'Edit loaded', __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);

            return view('main.subscription-plan.edit', compact('subscription', 'allSocialMediaApps', 'selectedSocialMediaIds'));
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Edit failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);
            return back()->with('error', 'Failed to load subscription');
        }
    }

    /**
     * Update the specified subscription plan in DB.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'subscription_title' => 'required|string|max:255',
                //'social_media'=>'required',
                'subscription_months' => [
                    'required',
                    'integer',
                    Rule::unique('subscriptions')->ignore($id)->where(function ($query) use ($request) {
                        return $query->where('subscription_title', $request->subscription_title)
                            ->whereNull('deleted_at');
                    }),
                ],
                'subscription_per_month_price' => 'required|numeric|min:0',
                'from_date' => 'required|date',
                'discount_type' => 'nullable|in:flat,percentage',
                'currency_code' => 'Currency code is required.',
            ];

            $messages = [
                'subscription_title.required' => 'The subscription title is required.',
                'subscription_title.string'   => 'The subscription title must be a string.',
                'subscription_title.max'      => 'The subscription title may not be greater than 255 characters.',
                //'social_media.required'        => 'Social media is required.',
                'subscription_months.required' => 'The number of months is required.',
                'subscription_months.integer'  => 'The number of months must be an integer.',
                'subscription_months.unique'   => 'A subscription with this title and number of months already exists.',

                'subscription_per_month_price.required' => 'The price per month is required.',
                'subscription_per_month_price.numeric'  => 'The price must be a valid number.',
                'subscription_per_month_price.min'      => 'The price must be at least 0.',

                'from_date.required' => 'The start date is required.',
                'from_date.date'     => 'The start date must be a valid date.',
                'discount_value.required' => 'The discount is required.',
                'discount_value.numeric' => 'The discount must be a number.',
                'discount_value.min' => 'The discount must be at least :min.',
                'discount_value.max' => 'The discount cannot be more than :max.',
                'currency.required' => 'Currency code is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);


            $validator->sometimes('discount_value', 'required|numeric|min:1|max:100', function ($input) {
                return $input->discount_type === 'percentage';
            });

            $validator->sometimes('discount_value', 'required|numeric|min:0', function ($input) {
                return $input->discount_type === 'flat';
            });

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()]);
            }

            $sub = Subscription::findOrFail($id);
            $from = Carbon::parse($request->from_date);
            $to = Carbon::parse($request->to_date);

            $discountType = $request->discount_type;
            $discountValue = $request->discount_value;
            $total = $request->subscription_months * $request->subscription_per_month_price;

            if ($discountType === 'flat') {
                $total -= $discountValue;
            } elseif ($discountType === 'percentage') {
                $total -= ($total * $discountValue / 100);
            }

            if ($total < 0) $total = 0;
            $sub->subscription_title = $request->subscription_title;
            $sub->subscription_months = $request->subscription_months;
            $sub->subscription_per_month_price = $request->subscription_per_month_price;
            $sub->subscription_total_price = $total;
            $sub->discount_type = $discountType;
            $sub->discount_value = $discountValue;
            $sub->from_date = $from;
            $sub->to_date = $to;
            $sub->currency_code = $request->currency;
            $sub->is_active = $request->has('is_active') ? 1 : 0;
            $sub->save();

            //delete previous one

            /*SubscriptionPlanSocialMedia::where('subscription_id', $sub->id)
              ->update(['is_active' => 0,'deleted_at'=>now()]);

			 // Insert social media relations
			foreach ($request->social_media as $socialMediaId) {
				SubscriptionPlanSocialMedia::create([
					'subscription_id' => $sub->id,
					'social_media_id' => $socialMediaId
				]);
			}*/


            DB::commit();

            LogHelper::logSuccess('success', 'Subscription updated', __FUNCTION__, __FILE__, __LINE__, request()->path(), $sub->id);
            return response()->json(['status' => 200, 'message' => 'Subscription updated']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::logError('exception', 'Update failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);
            return response()->json(['status' => 'error', 'message' => 'Update failed'], 500);
        }
    }
    /**
     * Soft-delete a subscription plan.
     */
    public function destroy($id)
    {
        try {
            $sub = Subscription::find($id);
            if (!$sub) {
                return response()->json(['success' => false, 'message' => 'Subscription not found']);
            }

            $subscriptions = SubscriptionPurchase::where("subscription_id", $id)
                     ->count();

			if ($subscriptions > 0) {
				return response()->json([
					'success' => false,
					'message' => 'Unable to delete records. This subscription is currently assigned to one or more company.'
				]);
			}

            $sub->is_active = 0;
            $sub->deleted_at = now();
            $sub->save();

            LogHelper::logSuccess('success', 'Subscription deleted', __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Delete failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);
            return response()->json(['success' => false, 'message' => 'Delete failed'], 500);
        }
    }
    /**
     * Display details of a specific subscription plan.
     */
    public function show($id)
    {
        try {
            $subscription = Subscription::with('subscriptionPlanSocialMedia.socialMediaApp')->findOrFail($id);

            // Get all social media apps for selection
            $allSocialMediaApps = SocialMediaApp::where('is_active', 1)
                ->whereNull('deleted_at')
                ->get();

            // Get only the selected social media app IDs for this subscription
            $selectedSocialMediaIds = $subscription->subscriptionPlanSocialMedia
                ->pluck('social_media_id')
                ->toArray();

            return view('main.subscription-plan.show', compact('subscription', 'allSocialMediaApps', 'selectedSocialMediaIds'));
        } catch (\Exception $e) {
            LogHelper::logError('exception', 'Show failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);
            return back()->with('error', 'Subscription not found');
        }
    }
}
