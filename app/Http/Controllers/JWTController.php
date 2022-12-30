<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class JWTController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:api', ['except'=> ['login','register']]);
    }


    //api response register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:200',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',

        ]);

        if ($validator->fails())
         {
            return response()->json($validator->errors(), 500);
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=>Hash::make($request->password)
        ]);



        return response()->json([
            'message' => 'User success register',
            'data' => $user
        ],200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails())
         {
            return response()->json($validator->errors(),422);
        }

       if (!$token = auth()->attempt($validator->validated()))
       {
        return response()->json(['error' => 'Failed Login'], 401);
       }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User success logout']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    public function profile()
    {
        return response()->json(auth()->user());
    }


    //token here

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()
        ]);
    }
}
