<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchHistory extends BaseModel
{
    use HasFactory;

    protected $table = 'search_history';

    protected $fillable = [
        'user_id', 'keyword', 'from'
    ];
}
