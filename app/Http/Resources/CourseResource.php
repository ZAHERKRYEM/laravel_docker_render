<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
         
            'id' => $this->id,
            'subject' => $this->subject_id,
            'title' => $this->title,
            'image_url' => $this->image,
            'description' => $this->description,
            "start_date" => Carbon::parse($this->start_date)->format('Y-m-d'), 
            "end_date" => Carbon::parse($this->end_date)->format('Y-m-d'),
            'student_year' => $this->student_year,
            'is_active' => $this->is_active
            
        ];
    }
}
