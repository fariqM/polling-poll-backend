<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
