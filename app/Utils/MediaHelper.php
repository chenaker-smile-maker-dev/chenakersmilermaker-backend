<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;

class MediaHelper
{
    /**
     * Format a single media item for API response.
     *
     * @return array{original: string, thumb?: string}|null
     */
    public static function single(Model $model, string $collection): ?array
    {
        $media = $model->getFirstMedia($collection);
        if (!$media) return null;

        $result = ['original' => $media->getUrl()];
        foreach ($media->getGeneratedConversions() as $conversion => $generated) {
            if ($generated) {
                $result[$conversion] = $media->getUrl($conversion);
            }
        }
        // Always include thumb if the conversion is registered
        if (!isset($result['thumb'])) {
            $thumbUrl = $model->getFirstMediaUrl($collection, 'thumb');
            if ($thumbUrl) {
                $result['thumb'] = $thumbUrl;
            }
        }
        return $result;
    }

    /**
     * Format multiple media items for API response.
     *
     * @return list<array{id: int, original: string, thumb?: string}>
     */
    public static function collection(Model $model, string $collection): array
    {
        return $model->getMedia($collection)->map(function ($media) {
            $result = [
                'id' => $media->id,
                'original' => $media->getUrl(),
            ];
            foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                if ($generated) {
                    $result[$conversion] = $media->getUrl($conversion);
                }
            }
            if (!isset($result['thumb'])) {
                $thumbUrl = $media->getUrl('thumb');
                if ($thumbUrl) {
                    $result['thumb'] = $thumbUrl;
                }
            }
            return $result;
        })->toArray();
    }
}
