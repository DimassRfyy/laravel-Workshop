<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'icon'
    ];

    public function setNameAttribute($value) {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function workshop(): HasMany {
        return $this->hasMany(Workshop::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            if ($category->icon) {
                Storage::delete($category->icon);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('icon')) {
                Storage::delete($category->getOriginal('icon'));
            }
        });
    }
}
