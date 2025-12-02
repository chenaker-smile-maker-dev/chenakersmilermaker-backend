<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Training extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\TrainingFactory> */
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'trainer_name',
        'duration',
        'documents',
        'video_url',
    ];

    public array $translatable = [
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'documents' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'image') {
            $this->addMediaConversion('thumb')
                ->width(300)
                ->height(200);

            $this->addMediaConversion('hero')
                ->width(1200)
                ->height(400);
        }
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image');
    }

    public function getImageThumbUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image', 'thumb');
    }
}
