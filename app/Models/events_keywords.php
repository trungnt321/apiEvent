<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class events_keywords extends Model
{
    use HasFactory;
    protected $fillable = ['keywords_id','event_id'];
}
