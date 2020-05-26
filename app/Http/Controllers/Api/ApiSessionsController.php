<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiSessionsController extends \App\Http\Controllers\Controller
{
    //

    public function destroy() {
        if (Auth::check()) {
            Auth::user()->AuthAcessToken()->delete();
        }
        return response()->json([
            'status' => '200',
            'message' => 'User logged out',
        ]);
    }

    public function store(Request $request) {
        //Validate form
        $validator = Validator::make($request->all(),[

            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => '400',
                'message' => 'Login form validation failed',
                'errors' => $validator->errors()
            ]);
        }
        try {
            //Authenticate user
            if( auth()->attempt(request(['email', 'password'])) ) {
                $user = Auth::user();
                $token = $user->createToken('appToken')->accessToken;

                //Return response
                return response()->json([
                    'status' => 200,
                    'message' => 'User logged in successfully',
                    'body' => ['userId' => $user->id,
                                'userName' => $user->firstName. ' ' .$user->lastName,
                                'token' => $token]

                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'User authentication failed',
                ]);
            }
        } catch (\Exception $e) {

            logger()->error($e);

            return response()->json([
                'status' => 400,
                'message' => 'User authentication failed',
                'error' => $e->getMessage(),

            ]);
        }
    }

    public function getAuthUser($userId) {

        $user = User::where('id', '=', $userId)->first();

        if($user){
            return response()->json([
                'status' => 200,
                'message' => 'User found',
                'body' => $user,
            ]);
        }else {
            return response()->json([
                'status' => 400,
                'message' => 'User not found',
            ]);
        }
    }
}
