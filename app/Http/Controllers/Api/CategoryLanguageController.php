<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Add this line
use Illuminate\Support\Facades\DB;
use App\Models\RecentHistory;

class CategoryLanguageController extends Controller
{
    public function admin_notifications(Request $request)
    {
        if (!$request->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required'
            ], 200);
        }
    
        $notifications = DB::table('notification_admin')
            ->where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')

            ->get();
    
        return response()->json([
            'status' => true,
            'data' => $notifications
        ], 200);
    }

    public function game_subcat_sliders()
    {
        try {
            $data = DB::table('game_subcat_sliders')
                ->select('id', 'image', 'created_at', 'updated_at')
                ->get();
    
            return response()->json([
                'status' => 200,
                'message' => 'Data fetched successfully',
                'data' => $data
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getRecentHistory(Request $request)
    {
        $request->validate([
            'user_id' => 'required'
        ]);
    
        $history = RecentHistory::where('user_id', $request->user_id)
            ->latest()
            ->take(10)
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Latest 10 history fetched successfully',
            'data' => $history
        ]);
    }

    public function storeGameHistory(Request $request)
    {
        $request->validate([
            'user_id'      => 'required',
            'cat_id'       => 'required',
            'cat_name'     => 'required',
            'sub_cat_id'   => 'required',
            'sub_cat_name' => 'required',
            'image'        => 'nullable'
        ]);
    
        $history = RecentHistory::create([
            'user_id'      => $request->user_id,
            'cat_id'       => $request->cat_id,
            'cat_name'     => $request->cat_name,
            'sub_cat_id'   => $request->sub_cat_id,
            'sub_cat_name' => $request->sub_cat_name,
            'image'        => $request->image,
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'History saved successfully',
            'data' => $history
        ]);
    }
    
    public function get_casino_lobby()
    {
        $casino_lobby = DB::table('casino_lobby')
            ->select('id', 'cat_id', 'cat_name', 'image', 'created_at', 'updated_at')
            ->get();
    
        return response()->json([
            'status' => true,
            'data' => $casino_lobby
        ], 200);
    }
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
