<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'name',
        'slug',
        'website',
        'industry',
        'location',
        'company_size',
        'description',
        'logo_url',
        'thumbnail_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
