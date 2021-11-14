<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    public function users()
{
   return $this->belongsToMany(
        User::class,
        'friend__users',
        'user_id',
        'friend_id');
}
}
