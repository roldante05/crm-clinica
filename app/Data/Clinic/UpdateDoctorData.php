<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateDoctorData extends Data
{
    public function __construct(
        public int $specialty_id,
        #[Unique('doctors', 'license_number', ignore: new RouteParameterReference('doctor'))]
        public string $license_number,
        public ?string $phone,
        public ?bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'specialty_id' => ['required', 'exists:specialties,id'],
            'license_number' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }
}
