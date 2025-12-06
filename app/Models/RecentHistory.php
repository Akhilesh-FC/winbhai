<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentHistory extends Model
{
    protected $table = "recent_history";

    protected $fillable = [
        'user_id',
        'cat_id',
        'cat_name',
        'sub_cat_id',
        'sub_cat_name',
        'image'
    ];
}
