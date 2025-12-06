<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoryadminLanguageController extends Controller
{
    public function index(Request $request)
    {
        $categories = DB::table('categories')->get();
        $languages  = DB::table('languages')->get();

        $query = DB::table('category_language as cl')
            ->join('categories as c','cl.category_id','=','c.id')
            ->join('languages as l','cl.language_id','=','l.id')
            ->select(
                'cl.id','c.name as category','l.name as language',
                'cl.image_url','cl.video_url'
            );

        if ($request->category_id)
            $query->where('cl.category_id',$request->category_id);

        if ($request->language_id)
            $query->where('cl.language_id',$request->language_id);

        $media = $query->get();

        return view('categorylanguage.index', compact(
            'categories','languages','media'
        ));
    }

    // CATEGORY
    public function storeCategory(Request $r){
        DB::table('categories')->insert(['name'=>$r->name]);
        return back();
    }
    public function updateCategory(Request $r,$id){
        DB::table('categories')->where('id',$id)->update(['name'=>$r->name]);
        return back();
    }

    // LANGUAGE
    public function storeLanguage(Request $r){
        DB::table('languages')->insert(['name'=>$r->name]);
        return back();
    }
    public function updateLanguage(Request $r,$id){
        DB::table('languages')->where('id',$id)->update(['name'=>$r->name]);
        return back();
    }

    // MEDIA
    public function storeMedia(Request $r){
        DB::table('category_language')->insert([
            'category_id'=>$r->category_id,
            'language_id'=>$r->language_id,
            'image_url'=>$r->image_url,
            'video_url'=>$r->video_url,
        ]);
        return back();
    }

    public function updateMedia(Request $r,$id){
        DB::table('category_language')->where('id',$id)->update([
            'image_url'=>$r->image_url,
            'video_url'=>$r->video_url,
        ]);
        return back();
    }

    public function deleteMedia($id){
        DB::table('category_language')->where('id',$id)->delete();
        return back();
    }
}
