<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    use HasFactory;
    protected $fillable = ['content','receiver_id'];


    public function user_receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id','id');
    }
}
