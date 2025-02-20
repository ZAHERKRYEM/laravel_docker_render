<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Subject;

class SubjectController extends Controller
{
    
    public function store(Request $request)
    {
        
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Unauthorized access.",
                "status_code" => 401
            ], 401);
        }

        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
                "message" => "Validation failed.",
                "status_code" => 422
            ], 422);
        }

    
      
        $course = Subject::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            "status" => true,
            "data" => $course,
            "message" => "Subject created successfully.",
            "status_code" => 201
        ], 201);
    }
}
