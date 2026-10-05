<?php

namespace App\Support;

class CuisineFlags
{
    private const MAP = [
        'American' => 'us',
        'British' => 'gb',
        'Canadian' => 'ca',
        'Chinese' => 'cn',
        'Croatian' => 'hr',
        'Dutch' => 'nl',
        'Egyptian' => 'eg',
        'Filipino' => 'ph',
        'French' => 'fr',
        'Greek' => 'gr',
        'Indian' => 'in',
        'Irish' => 'ie',
        'Italian' => 'it',
        'Jamaican' => 'jm',
        'Japanese' => 'jp',
        'Kenyan' => 'ke',
        'Malaysian' => 'my',
        'Mexican' => 'mx',
        'Moroccan' => 'ma',
        'Polish' => 'pl',
        'Portuguese' => 'pt',
        'Russian' => 'ru',
        'Spanish' => 'es',
        'Thai' => 'th',
        'Tunisian' => 'tn',
        'Turkish' => 'tr',
        'Ukrainian' => 'ua',
        'Vietnamese' => 'vn',
        'Unknown' => null,
    ];

    public static function forNames(array $names): array
    {
        $result = [];

        foreach ($names as $name) {
            $result[] = [
                'name' => $name,
                'code' => self::MAP[$name] ?? null,
            ];
        }

        return $result;
    }
}
