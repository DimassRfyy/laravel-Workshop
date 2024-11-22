<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Workshop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'venue_thumbnail',
        'bg_map',
        'address',
        'about',
        'price',
        'is_open',
        'has_started',
        'started_at',
        'time_at',
        'category_id',
        'workshop_instructor_id'
    ];

    protected $casts = [
        'started_at' => 'date',
        'time_at' => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($workshop) {
            if ($workshop->thumbnail) {
                Storage::delete($workshop->thumbnail);
            }
            if ($workshop->venue_thumbnail) {
                Storage::delete($workshop->venue_thumbnail);
            }
            if ($workshop->bg_map) {
                Storage::delete($workshop->bg_map);
            }
        });

        static::updating(function ($workshop) {
            if ($workshop->isDirty('thumbnail')) {
                Storage::delete($workshop->getOriginal('thumbnail'));
            }
            if ($workshop->isDirty('venue_thumbnail')) {
                Storage::delete($workshop->getOriginal('venue_thumbnail'));
            }
            if ($workshop->isDirty('bg_map')) {
                Storage::delete($workshop->getOriginal('bg_map'));
            }
        });
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function benefits(): HasMany {
        return $this->hasMany(WorkshopBenefit::class);
    }

    public function participants(): HasMany {
        return $this->hasMany(WorkshopParticipant::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(WorkshopInstructor::class, 'workshop_instructor_id');
    }
}
