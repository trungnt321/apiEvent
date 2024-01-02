<?php

namespace App\Models;

use App\Casts\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class resource extends Model
{
    use HasFactory;

    protected $fillable = ['name','url','event_id'];

    protected $casts = [
        'url' => Image::class,
    ];
}
