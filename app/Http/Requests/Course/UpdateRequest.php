<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization is handled by middleware
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'sometimes|required|string',
            'long_description' => 'sometimes|required|string',
            'path' => 'sometimes|required|string|unique:courses,path,' . $this->route('id'),
            'category_id' => 'sometimes|required|array|exists:categories,id',
            'aircraft_id' => 'sometimes|required|exists:aircraft,id'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Course title is required',
            'title.max' => 'Course title cannot exceed 255 characters',
            'short_description.required' => 'Short description is required',
            'long_description.required' => 'Long description is required',
            'path.required' => 'Path is required',
            'path.unique' => 'This path is already in use',
            'category_id.required' => 'At least one category must be selected',
            'category_id.exists' => 'One or more selected categories do not exist',
            'aircraft_id.required' => 'Aircraft must be selected',
            'aircraft_id.exists' => 'Selected aircraft does not exist'
        ];
    }
} 