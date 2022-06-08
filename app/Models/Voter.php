<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    use HasFactory;

    protected $fillable = [
        'answer_id',
        'name',
        'email',
        'device_id',
        'is_verified',
    ];

    public function answer(){
        return $this->belongsTo(Answer::class);
    }
}
