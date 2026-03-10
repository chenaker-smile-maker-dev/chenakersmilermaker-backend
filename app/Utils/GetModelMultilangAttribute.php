<?php

namespace App\Utils;

class GetModelMultilangAttribute
{
    /**
     * @return array{en: string, ar: string, fr: string}
     */
    public static function get($model, string $attribute): array
    {
        $locals = config('default-local.available_locals');
        $multilang = [];

        foreach ($locals as $local) {
            $multilang[$local] = $model->getTranslation($attribute, $local) ?? $model->{$attribute};
        }

        return $multilang;
    }
}
