<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
class Message extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'messages';
    protected $fillable = [
        'message',
        'reciever_id'
    ];
    public function user()
    {
    return $this->belongsTo(User::class);
    }
}
