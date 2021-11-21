<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
class Friend_User extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'user_friend';
}
