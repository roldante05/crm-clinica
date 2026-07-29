<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateSpecialtyData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?bool $is_active,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:specialties,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }


    public static function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre debe tener como máximo 255 caracteres',
            'name.unique' => 'El nombre ya existe',
            'description.nullable' => 'La descripción debe ser una cadena de texto',
            'description.max' => 'La descripción debe tener como máximo 1000 caracteres',
            'is_active.boolean' => 'El estado debe ser un booleano',
        ];
    }
}
