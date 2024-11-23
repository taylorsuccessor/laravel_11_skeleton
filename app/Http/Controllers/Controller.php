<?php

/**
 * Description of file.
 *
 * This file contains the Controller class which
 * serves as the base class for all controllers.
 *
 * @category  Category
 * @package   App\Http\Controllers
 * @author    Hashim <taylorsuccessor@gmail.com>
 * @copyright 2024 Hashim
 * @license   https://hashim.com/licenses/M M License
 * @version   1.0.0
 * @link      https://hashim.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use GeoIP;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    // TODO[Hashim]: delete if not needed
    /**
     * @param  array|string $scopes
     * @return bool
     */
    public function authorizedFor($scopes)
    {
        return true;

        if (auth()->user()->is_admin) {
            return true;
        }

        if (auth()->user()->tokenCan($scopes)) {
            return true;
        }

        abort(403);
    }

    public function getAuthenticatedUser(Request $request)
    {
        try {
            return $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function getUserCountry()
    {
        $location = geoip()->getLocation(request()->ip());
        return $location->country; // Example: "United States"
    }
}
