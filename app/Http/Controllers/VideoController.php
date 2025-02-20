<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\Video;
class VideoController extends Controller
{
    public function index($course_id)
    {
        try {
            $videos = Video::where('course_id', $course_id)->get();

            // التأكد إذا كان هناك فيديوهات
            if ($videos->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'message' => 'No videos found for this course.',
                    'status_code' => 200,
                ], 200);
            }

            $formattedCourses = $videos->map(function ($video) {
                return [
                    "id" => $video->id,
                    "course" => $video->course_id,
                    "teacher" => $video->teacher->user->username,
                    "title" => $video->title,
                    "url" => $video->url,
                    "description" => $video->description, 
                    "upload_date" => Carbon::parse($video->updated_at)->format('Y-m-d'),
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $formattedCourses,
                'message' => 'Videos retrieved successfully',
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    // إضافة فيديو جديد
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
        $validated =Validator::make($request->all(), [
            'course_id' => 'required',
            'teacher_id' => 'required',
            'title' => 'required|string|max:200',
            'url' => 'required',
            'description' => 'nullable|string',
        ]);
        if ($validated->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validated->errors(),
                "message" => "Validation failed.",
                "status_code" => 422
            ], 422);
        }

        try {
            $video = Video::create([
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id,
                'title' =>$request->title,
                'url' => $request->url,
                'description' => $request->description
                
            ]);

            

            return response()->json([
                'status' => true,
                'data' => $video,
                'message' => 'Video created successfully',
                'status_code' => 201,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'status_code' => 400,
            ], 400);
        }
    }
}
