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
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    public function redirect(Request $request)
    {
        Log::info('Instagram OAuth redirect', ['request' => $request->all()]);
    }
    public function callback(Request $request)
    {
       
        Log::info('Instagram OAuth callback', ['request' => $request->all()]);
       
    }     
}