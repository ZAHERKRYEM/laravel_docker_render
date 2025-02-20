<?php

namespace App\Http\Controllers;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Exception;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{
    public function index()
{
    try {
      
        $courses = Course::all();

        if ($courses->isEmpty()) {
            return response()->json([
                "status" => true,
                "data" => [],
                "message" => "No courses available.",
                "status_code" => 200
            ], 200);
        }
        $formattedCourses = $courses->map(function ($course) {
            return [
                "id" => $course->id,
                "subject" => $course->subject_id,
                "title" => $course->title,
                "image_url" => $course->image,
                "description" => $course->description,
                "start_date" => Carbon::parse($course->start_date)->format('Y-m-d'), // فقط التاريخ
                "end_date" => Carbon::parse($course->end_date)->format('Y-m-d'),
                "student_year" => $course->student_year,
                "is_active" => $course->is_active
            ];
        });
     

        return response()->json([
            "status" => true,
            "data" => $formattedCourses,
            "message" => "All courses retrieved successfully.",
            "status_code" => 200
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            "status" => false,
            "data" => [],
            "message" => "An error occurred: " . $e->getMessage(),
            "status_code" => 500
        ], 500);
    }
}

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
            'title' => 'required|string|max:200',
            'subject_id' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'student_year' => 'nullable|string|max:200',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
                "message" => "Validation failed.",
                "status_code" => 422
            ], 422);
        }

     
        $uploadedFileUrl = null;
        if ($request->hasFile('image')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
        }

      
        $course = Course::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'image' => $uploadedFileUrl,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'student_year' => $request->student_year,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            "status" => true,
            "data" => [
                "id"=> $course->id,
                "subject"=> $course->subject_id,
                "title"=> $course->title,
                "image_url"=> $course->image,
                "description"=> $course->description,
                "start_date" => Carbon::parse($course->start_date)->format('Y-m-d'), 
                "end_date" => Carbon::parse($course->end_date)->format('Y-m-d'),
                "student_year"=> $course->student_year,
                "is_active"=> $course->is_active
              
            ],
            "message" => "Course created successfully.",
            "status_code" => 201
        ], 201);
    }


    public function search(Request $request)
    {
       
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Unauthorized access.",
                "status_code" => 401
            ], 401);
        }

        
        $title = $request->query('title');

        if (!$title) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Title parameter is required.",
                "status_code" => 400
            ], 400);
        }

        
        $courses = Course::where('title', 'like', "%$title%")->get();
        $formattedCourses = $courses->map(function ($course) {
            return [
                "id" => $course->id,
                "subject" => $course->subject_id,
                "title" => $course->title,
                "image_url" => $course->image,
                "description" => $course->description,
                "start_date" => Carbon::parse($course->start_date)->format('Y-m-d'), 
                "end_date" => Carbon::parse($course->end_date)->format('Y-m-d'),
                "student_year" => $course->student_year,
                "is_active" => $course->is_active
            ];
        });
        return response()->json([
            "status" => true,
            "data" => $formattedCourses,
            "message" => count($courses) > 0 ? "Courses found successfully." : "No courses found.",
            "status_code" => 200
        ], 200);
    }


    public function byyear(Request $request){
        {
            try {
                
                $user = JWTAuth::parseToken()->authenticate();
                $student = Student::where('user_id', $user->id)->first();
    
                if (!$student) {
                    return response()->json([
                        "status" => false,
                        "data" => [],
                        "message" => "Logged-in user is not associated with a student account.",
                        "status_code" => 400
                    ], 400);
                }
    
                $student_year = $student->student_year;
    
                if (!$student_year) {
                    return response()->json([
                        "status" => false,
                        "data" => [],
                        "message" => "Student year is not set for the logged-in student.",
                        "status_code" => 400
                    ], 400);
                }
    
               
                $courses = Course::where('student_year', $student_year)->inRandomOrder()->limit(4)->get();
    
                if ($courses->isEmpty()) {
                    return response()->json([
                        "status" => true,
                        "data" => [],
                        "message" => "No course found for the given student year.",
                        "status_code" => 200
                    ], 200);
                }

                $formattedCourses = $courses->map(function ($course) {
                    return [
                        "id" => $course->id,
                        "subject" => $course->subject_id,
                        "title" => $course->title,
                        "image_url" => $course->image,
                        "description" => $course->description,
                        "start_date" => Carbon::parse($course->start_date)->format('Y-m-d'), 
                        "end_date" => Carbon::parse($course->end_date)->format('Y-m-d'),
                        "student_year" => $course->student_year,
                        "is_active" => $course->is_active
                    ];
                });
    
                return response()->json([
                    "status" => true,
                    "data" => $formattedCourses,
                    "message" => "Course found successfully.",
                    "status_code" => 200
                ], 200);
    
            } catch (Exception $e) {
                return response()->json([
                    "status" => false,
                    "data" => [],
                    "message" => "An error occurred: " . $e->getMessage(),
                    "status_code" => 500
                ], 500);
            }
        }

    }

    public function oneperyear(Request $request){
        try {
          
            $years = Course::distinct()->pluck('student_year');

           
            $courses = [];

           
            foreach ($years as $year) {
                $course = Course::where('student_year', $year)->inRandomOrder()->first();
                if ($course) {
                    $courses[] = $course;
                }
            }

           
            if (count($courses) > 0) {
                
                return response()->json([
                    "status" => true,
                    "data" => CourseResource::collection($courses),
                    "message" => "Courses retrieved successfully.",
                    "status_code" => 200
                ], 200);
            } else {
                return response()->json([
                    "status" => true,
                    "data" => [],
                    "message" => "No courses found.",
                    "status_code" => 200
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "An error occurred: " . $e->getMessage(),
                "status_code" => 500
            ], 500);
        }
    }
}
