<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('applicationtoken')->plainTextToken;

        return response()->json(
            [
                'sucess' => 'Registered!',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ],
            201
        );
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('applicationtoken')->plainTextToken;
        } else {
            throw new HttpResponseException(response()->json(['error' => 'Bad Credentials!'], 400));
        }

        return response()->json(
            [
                'sucess' => 'Logged in!',
                'data' => [
                    'token' => $token
                ]
            ],
            200
        );
    }

    public function logout()
    {
        $user = User::find(Auth::user()->id);
        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out!'], 200);
    }
}
