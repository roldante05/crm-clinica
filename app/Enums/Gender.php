<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
    case Unspecified = 'unspecified';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Masculino',
            self::Female => 'Femenino',
            self::Other => 'Otro',
            self::Unspecified => 'No especificado',
        };
    }
}
