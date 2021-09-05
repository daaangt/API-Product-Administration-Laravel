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
    /**
     * @OA\Post(
     *    path="/register",
     *    summary="Sign up",
     *    description="Registering user",
     *    operationId="register",
     *    tags={"Authentication"},
     *    @OA\RequestBody(
     *       required=true,
     *       description="Pass user credentials",
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", format="text", example="UserName"),*
     *             @OA\Property(property="email", type="string", format="email", example="user_mail@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345"),
     *          )
     *       )
     *    ),
     *    @OA\Response(
     *       response=201,
     *       description="Register successful!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Registered!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=422,
     *       description="Missing informations!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Missing informations!")
     *       )
     *    )
     * )
     */
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

    /**
     * @OA\Post(
     *    path="/login",
     *    summary="Sign in",
     *    description="Login by email, password",
     *    operationId="login",
     *    tags={"Authentication"},
     *    @OA\RequestBody(
     *       required=true,
     *       description="Pass user credentials",
     *       @OA\JsonContent(
     *          required={"email", "password", "password_confirmation"},
     *          @OA\Property(property="email", type="string", format="email", example="user_mail@gmail.com"),
     *          @OA\Property(property="password", type="string", format="password", example="12345"),
     *          @OA\Property(property="password_confirmation", type="string", format="password", example="12345"),
     *       ),
     *    ),
     *    @OA\Response(
     *       response=200,
     *       description="Logged in!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Logged in!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=400,
     *       description="Bad Credentials!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Bad Credentials!")
     *       )
     *    )
     * )
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
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

    /**
     * @OA\Post(
     *    path="/logout",
     *    summary="Logout",
     *    description="Destroy the token and disconnect the user",
     *    operationId="logout",
     *    tags={"Authentication"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\Response(
     *       response=200,
     *       description="Logged out!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Logged out!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=401,
     *       description="Unauthorized!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Unauthorized!")
     *       )
     *    )
     * )
     */
    public function logout()
    {
        $user = User::find(Auth::user()->id);
        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out!'], 200);
    }
}
