<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Add this line
use Illuminate\Support\Facades\DB;

class CategoryLanguageController extends Controller
{
    
     // 🔹 Filter by category_id + language_id
   public function getFilteredData(Request $request)
{
    $category_id = $request->input('category_id');
    $language_id = $request->input('language_id');

    $query = DB::table('category_language as cl')
        ->join('categories as c', 'cl.category_id', '=', 'c.id')
        ->join('languages as l', 'cl.language_id', '=', 'l.id')
        ->select(
            'c.name as category_name',
            'l.name as language_name',
            'cl.image_url',
            'cl.video_url',
            'cl.title',
            'cl.description'
        );

    // ✅ Apply filters only if given
    if ($category_id) {
        $query->where('cl.category_id', $category_id);
    }

    if ($language_id) {
        $query->where('cl.language_id', $language_id);
    }

    // ✅ Fetch all or filtered data
    $data = $query->get();

    if ($data->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No media found for this category and language.'
        ]);
    }

    return response()->json([
        'status' => true,
        'data' => $data
    ]);
}

    
    
    
    
    // 🔹 API: GET /api/category-language-data
    public function getData(Request $request)
    {
        $category_id = $request->input('category_id');
        $language_id = $request->input('language_id');

        // Base query with joins
        $query = DB::table('category_language as cl')
            ->join('categories as c', 'cl.category_id', '=', 'c.id')
            ->join('languages as l', 'cl.language_id', '=', 'l.id')
            ->select(
                'c.id as category_id',
                'c.name as category_name',
                'l.id as language_id',
                'l.name as language_name',
                'cl.image_url',
                'cl.video_url'
            );

        // Filter by category/language if provided
        if ($category_id) {
            $query->where('cl.category_id', $category_id);
        }
        if ($language_id) {
            $query->where('cl.language_id', $language_id);
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No data found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
