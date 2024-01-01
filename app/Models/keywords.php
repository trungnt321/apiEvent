<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class keywords extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasManyThrough(
            event::class,
            events_keywords::class,
            'keywords_id', // Khóa ngoại của bảng trung gian
            'id', // Khóa chính của bảng keywords
            'id', // Khóa chính của bảng events
            'event_id' // Khóa ngoại của bảng keywords
        );
    }
}
