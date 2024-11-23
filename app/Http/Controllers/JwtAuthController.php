<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\ValidationException;

class JwtAuthController extends Controller
{
    public function getJwtToken(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return response()->json(['token' => JWTAuth::fromUser($user)]);
        }
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'incorrect_access_data'], 401);
        }

        return response()->json(['token' => $token]);
    }
}
