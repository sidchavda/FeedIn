<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'link',
        'language_id',
        'category_id',
        'description',
        'image',
        'author',
        'status',
        'push_notification',
        'country_id',
        'state_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            $news->slug = $news->generateUniqueSlug($news->title);
        });

        static::updating(function ($news) {
            if ($news->isDirty('title')) {
                $news->slug = $news->generateUniqueSlug($news->title);
            }
        });

        static::saving(function ($news) {
            foreach (['title', 'description', 'author'] as $field) {
                if (isset($news->$field) && is_string($news->$field)) {
                    $news->$field = html_entity_decode($news->$field, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        });
    }

    public function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
