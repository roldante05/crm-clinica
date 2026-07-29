<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateSpecialtyData extends Data
{
    public function __construct(
        #[Unique('specialties', 'name', ignore: new RouteParameterReference('specialty'))]
        public string $name,
        public ?string $description,
        public ?bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}
