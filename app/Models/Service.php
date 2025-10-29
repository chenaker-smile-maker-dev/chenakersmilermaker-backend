<?php

namespace App\Models;

use App\Enums\Service\ServiceAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia, HasTranslations;

    protected $fillable = ['name', 'price', 'active', 'availability'];
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            // 'name' => 'array',
            'price' => 'integer',
            'active' => 'boolean',
            'availability' => ServiceAvailability::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'image') {
            $this->addMediaConversion('thumb')
                ->width(100)
                ->height(100);
        }
    }

    public function getImageAttribute()
    {
        return $this->hasMedia('image')
            ? $this->getFirstMediaUrl('image')
            : null;
    }

    public function getThumbImageAttribute()
    {
        return $this->hasMedia('image')
            ? $this->getFirstMediaUrl('image', 'thumb')
            : null;
    }
    public function getAvailabilityDisplayAttribute(): string|null
    {
        return $this->availability->value;
    }
}
