<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language_id',
        'is_active',
    ];

    public function getIsActiveAttribute($value)
    {

        return ($value == 1) ? 'Active' : 'In Active';
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
