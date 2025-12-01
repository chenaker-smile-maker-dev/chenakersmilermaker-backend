<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    use HasTranslations;
    use HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'date',
        'is_archived',
        'location',
    ];

    public array $translatable = [
        'title',
        'slug',
        'description',
        'location',
    ];

    protected function casts(): array
    {
        return [
            // 'title' => 'array',
            // 'slug' => 'array',
            // 'description' => 'array',
            // 'location' => 'array',
            'date' => 'date',
            'is_archived' => 'boolean',
        ];
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
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

    public function getGalleryAttribute()
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return [
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
            ];
        });
    }
}
