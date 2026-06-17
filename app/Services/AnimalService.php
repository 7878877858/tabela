<?php

namespace App\Services;

class AnimalService
{
    public static function generateTag(string $animalType): string
    {
        return AnimalTagService::generate($animalType);
    }

    public static function previewTag(string $animalType): string
    {
        return AnimalTagService::preview($animalType);
    }
}
