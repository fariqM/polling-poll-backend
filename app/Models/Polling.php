<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    use HasFactory;
    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'owner_id',
        'dir',
        'question',
        'description',
        'q_img',
        'deadline',
        'with_password',
        'password',
        'with_area_res',
        'area',
        'with_device_res',
        'req_email',
        'req_name',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function voters()
    {
        return $this->hasManyThrough(Voter::class, Answer::class);
    }

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($polling) {
            $polling->answers()->delete();
        });
    }
}
