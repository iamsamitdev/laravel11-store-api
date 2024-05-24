<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Registrate",
     *     operationId="Register",
     *
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="fullname",
     *                     description="Enter your fullname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     description="Enter your username",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="Enter your Email",
     *                     type="email"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="Enter your password",
     *                     type="password"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="Enter your password confirmation",
     *                     type="password"
     *                 ),
     *                 @OA\Property(
     *                     property="tel",
     *                     description="Enter your tel",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="role",
     *                     description="Enter your role",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    // Register User
    public function register(Request $request){

        // Validate field
        $fields = $request->validate([
            'fullname' => 'required|string',
            'username' => 'required|string',
            'email'=> 'required|string|unique:users,email',
            'password'=>'required|string|confirmed',
            'tel'=>'required',
            'role'=> 'required|integer'
        ]);

        // Create user
        $user = User::create([
            'fullname' => $fields['fullname'],
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']), 
            'tel' => $fields['tel'],
            'role' => $fields['role']
        ]);

        $response = [
            'status' => true,
            'message' => "User registered successfully",
            'user' => $user,
        ];

        return response($response, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Authentificate",
     *     operationId="Login",
     *
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     description="Enter your email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="Enter password",
     *                     type="password"
     *                 )
     *             )
     *         )
     *     )
     * )
     */    

    // Login User
    public function login(Request $request) {

        // Validate field
        $fields = $request->validate([
            'email'=> 'required|string',
            'password'=>'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'status' => false,
                'message' => 'Login failed'
            ], 401);
        }else{
            
            // ลบ token เก่าออกแล้วค่อยสร้างใหม่
            $user->tokens()->delete();

            // Create token
            $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;
    
            $response = [
                'status' => true,
                'message' => 'Login successfully',
                'user' => $user,
                'token' => $token
            ];
    
            return response($response, 201);
        }

    }

    // Refresh Token
    public function refreshToken(Request $request) {
        $user = $request->user();
        // ลบ token เก่าออกแล้วค่อยสร้างใหม่
        $user->tokens()->delete();

        // Create token
        $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;
        $response = [
            'status' => true,
            'message' => 'Token refreshed',
            'user' => $user,
            'token' => $token
        ];
        return response($response, 201);
    }

    // Logout User
    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'status' => true,
            'message' => 'Logged out'
        ];
    }

}
