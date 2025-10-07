<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Doctor extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia, HasTranslations;


    protected $fillable = ['name', 'specialty', 'diplomas'];
    public array $translatable = ['name', 'specialty'];
    protected $hidden = [];

    protected function casts(): array
    {
        return [
            // 'name' => 'array',
            'specialty' => 'array',
            'diplomas' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('doctor_photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }
    // register media conversions for the media doctor photo
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'doctor_photo') {
            $this->addMediaConversion('thumb')
                ->width(width: 100)
                ->height(100);
        }
    }

    public function getImageAttribute()
    {
        return $this->hasMedia('doctor_photo')
            ? $this->getFirstMediaUrl('doctor_photo')
            : null;
    }

    public function getThumbImageAttribute()
    {
        return $this->hasMedia('doctor_photo')
            ? $this->getFirstMediaUrl('doctor_photo', 'thumb')
            : null;
    }

    public function getDiplomasCountAttribute()
    {
        return is_array($this->diplomas) ? count($this->diplomas) : 0;
    }
}
