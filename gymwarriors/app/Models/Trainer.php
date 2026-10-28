<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'specialty',
        'experience',
        'description',
        'email',
        'phone',
        'image',
    ];
}
