<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function getIsActiveAttribute($value)
    {
        return ($value == 1) ? 'Active' : 'In Active';
    }
}
