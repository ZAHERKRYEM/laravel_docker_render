<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    // User registration
    public function register(Request $request)
    {
        $request->validate([
            'user.username' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:6',
            'user.is_staff' => 'boolean',
            'profile' => 'required|array'
        ]);

        $userData = $request->input('user');
        $profileData = $request->input('profile');
        $isStaff = $userData['is_staff'] ?? false;

        // إنشاء المستخدم
        $user = User::create([
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'is_staff' => $isStaff
        ]);

        // إنشاء الملف الشخصي كـ Teacher أو Student
        if ($isStaff) {
            $profile = Teacher::create(array_merge($profileData, ['user_id' => $user->id]));
        } else {
            $profile = Student::create(array_merge($profileData, ['user_id' => $user->id]));
        }

        // إنشاء التوكنات
        $accesstoken = 'test';
       

        return response()->json([
            "status" => true,
            "data" => [
                "user" => $user,
                "profile" => $profile,
                "tokens" => [
                    "access" => $accesstoken,
                    "refresh" => $accesstoken
                ]
            ],
            "message" => "User and profile created successfully",
            "status_code" => 201
        ], 201);
    }

    public function login(Request $request)
{
    try {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Invalid credentials",
                "status_code" => 401
            ], 401);
        }

        $user = auth()->user();

        // جلب بيانات الملف الشخصي
        $profile = null;
        if ($user->is_staff) {
            $profile = Teacher::where('user_id', $user->id)->first();
        } else {
            $profile = Student::where('user_id', $user->id)->first();
        }

        if (!$profile) {
            throw new \Exception("User profile not found");
        }

        return response()->json([
            "status" => true,
            "data" => [
                "user" => [
                    "id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "is_staff" => $user->is_staff
                ],
                "profile" => [
                    "student_year" => $profile->student_year ?? null,
                    "student_id" => $profile->student_id ?? null
                ],
                "tokens" => [
                    "access" => $token,
                    "refresh" => $token
                ]
            ],
            "message" => "Login successful",
            "status_code" => 200
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            "status" => false,
            "data" => [],
            "message" => "User profile not found",
            "status_code" => 404
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            "status" => false,
            "data" => [],
            "message" => "An unexpected error occurred: " . $e->getMessage(),
            "status_code" => 500
        ], 500);
    }
}

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(Request $request)
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                "status" => true,
                "data" => ["access" => $newToken],
                "message" => "refresh successful",
                "status_code" => 200
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Token has expired.",
                "status_code" => 400
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Invalid token provided.",
                "status_code" => 401
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Token is missing or invalid.",
                "status_code" => 400
            ], 400);
        }
    }
}