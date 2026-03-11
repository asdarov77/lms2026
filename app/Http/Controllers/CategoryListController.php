<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;

class CategoryListController extends Controller
{
    public function getCatCourses(Request $request)
    {
        try {
            $categories = Category::select('id', 'title', 'code')->get();
            
            // Log the successful retrieval of categories
            \Log::info('Categories fetched successfully', ['count' => $categories->count()]);
            
            return response()->json($categories);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error fetching categories: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['message' => 'Error fetching categories: ' . $e->getMessage()], 500);
        }
    }

    public function getCatCoursesID($id)
    {
        try {
            $category = Category::findOrFail($id);
            $courses = $category->courses;
            
            // Load any additional relationships we need
            $courses->load('categories');
            
            \Log::info('Courses by category fetched successfully', [
                'category_id' => $id,
                'category_title' => $category->title,
                'courses_count' => $courses->count()
            ]);
            
            return response()->json($courses);
        } catch (\Exception $e) {
            \Log::error('Error fetching courses by category: ' . $e->getMessage(), [
                'category_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json(['message' => 'Error fetching courses: ' . $e->getMessage()], 500);
        }
    }
}
