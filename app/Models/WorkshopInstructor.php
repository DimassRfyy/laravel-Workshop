<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class WorkshopInstructor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'avatar',
        'occupation'
    ];

    public function workshops(): HasMany {
        return $this->hasMany(Workshop::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($instructor) {
            if ($instructor->avatar) {
                Storage::delete($instructor->avatar);
            }
        });

        static::updating(function ($instructor) {
            if ($instructor->isDirty('avatar')) {
                Storage::delete($instructor->getOriginal('avatar'));
            }
        });
    }
}
