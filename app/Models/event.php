<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    use HasFactory;
    protected $fillable = ['name','location','contact','status','banner','user_id','start_time','end_time'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(atendance::class);
    }
    public function feedback()
    {
        return $this->hasMany(feedback::class);
    }
}
