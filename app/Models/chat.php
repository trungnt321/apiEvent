<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    use HasFactory;
    protected $fillable = ['content','sender_id','event_id'];

    public function senderInfo()
    {
        return $this->belongsTo(User::class,'sender_id');
    }
}
