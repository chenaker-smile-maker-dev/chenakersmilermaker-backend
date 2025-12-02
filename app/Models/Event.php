<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'is_archived',
        'location',
    ];

    public array $translatable = [
        'title',
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
