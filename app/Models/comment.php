<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
class Comment extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'comments';
    protected $fillable = [
        'comment'
    ];

    public function post()
    {
        return $this->belongsTo(post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
