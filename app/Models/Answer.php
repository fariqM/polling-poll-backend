<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'polling_id',
        'text',
        'a_img',
    ];

    public function polling(){
        return $this->belongsTo(Polling::class);
    }

    public function voters(){
        return $this->hasMany(Voter::class);
    }
}
