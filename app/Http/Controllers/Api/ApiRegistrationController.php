<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ApiRegistrationController extends \App\Http\Controllers\Controller
{
    //
    public function store(Request $request) {

        //Validate form
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'phoneNumber' => 'required|digits:10',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Registration form validation failed',
                'errors' => $validator->errors()
            ]);
        }
        try{
            //Hash password
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);

            //Create and save
            $user = User::create($input);

            //Create token
            $token = $user->createToken('appToken')->accessToken;
                //Return response
                return response()->json([
                    'status' => 200,
                    'message' => 'User created successfully',
                    'body' => ['userId' => $user->id,
                        'userName' => $user->firstName. ' ' .$user->lastName,
                        'token' => $token]
                ]);
        } catch (\Exception $e) {

            logger()->error($e);

            return response()->json([
                'status' => 400,
                'message' => 'User registration failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendPasswordResetToken(Request $request)
    {
        $user = User::where('email', $request->email)-first();
        if ( !$user ) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid email',
            ]);
        }

        //create a new token to be sent to the user.
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => str_random(60), //change 60 to any length you want
            'created_at' => Carbon::now()
        ]);

        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)->first();

        $token = $tokenData->token;
        $email = $request->email; // or $email = $tokenData->email;

        return response()->json([
            'message' => 'Please click on this link to reset your password',
            'token' => $token
        ]);

        /**
         * Send email to the email above with a link to your password reset
         * something like url('password-reset/' . $token)
         * Sending email varies according to your Laravel version. Very easy to implement
         */
    }

    public function resetPassword(Request $request, $token) {

        $validator = Validator::make($request->all(), [

            'password' => 'required|min:8',
            'confirmPassword' => 'required|min:8'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Password is invalid',
                'errors' => $validator->errors()
            ], 400);
        } elseif ($request['password'] != $request['confirmPassword'])

        $password = $request->password;
        $tokenData = DB::table('password_resets')
            ->where('token', $token)->first();

        $user = User::where('email', $tokenData->email)->first();
        if ( !$user ) {
            return response()->json([
                'message' => 'Invalid email',
            ], 400);
        }

        $user->password = Hash::make($password);
        $user->update(); //or $user->save();

        DB::table('password_resets')->where('email', $user->email)->delete();

        //Create token
        $auth_token = $user->createToken('appToken')->accessToken;
        //Return response
        return response()->json([
            'message' => 'User created successfully',
            'userId' => $user->id,
            'token' => $auth_token
        ], 200);

        // If the user shouldn't reuse the token later, delete the token


 }

}
