<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'server',
        'port',
        'security',
        'username',
        'password'
    ];
    public function getIsActiveAttribute($value)
    {
        return ($value == 1) ? 'Active' : 'In Active';
    }
}
