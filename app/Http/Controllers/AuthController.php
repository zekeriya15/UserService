<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;



class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'User successfully created',
            'user' => $user
        ]);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Unautheticated'
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'Success',
            'user' => $user,
            'authorization' => [
                'type' => 'Bearer',
                'token' => $token,
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function refresh() {
        return response()->json([
            'status' => 'Success',
            'user' => Auth::user(),
            'authorization' => [
                'type' => 'Bearer',
                'token' => Auth::refresh(),
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function logout() {
        // get token
        $token = JWTAuth::getToken();

        // invalidate token
        $invalidate = JWTAuth::invalidate($token);

        if($invalidate) {
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        }
    }
}
