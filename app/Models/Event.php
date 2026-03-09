<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'time',
        'is_archived',
        'location',
        'speakers',
        'about_event',
        'what_to_expect',
    ];

    public array $translatable = [
        'title',
        'description',
        'location',
        'speakers',
        'about_event',
        'what_to_expect',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_archived' => 'boolean',
        ];
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_archived) return 'archive';
        if ($this->date->isToday()) return 'happening';
        if ($this->date->isFuture()) return 'future';
        return 'archive';
    }

    public function scopeArchived($query)
    {
        return $query->where(function ($q) {
            $q->where('is_archived', true)->orWhere('date', '<', today());
        });
    }

    public function scopeHappening($query)
    {
        return $query->where('is_archived', false)->whereDate('date', today());
    }

    public function scopeFuture($query)
    {
        return $query->where('is_archived', false)->where('date', '>', today());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'gallery') {
            $this->addMediaConversion('thumb')
                ->width(width: 100)
                ->height(100);
        }
    }
}
