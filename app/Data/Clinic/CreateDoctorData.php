<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateDoctorData extends Data
{
    public function __construct(
        public int $user_id,
        public int $specialty_id,
        public string $license_number,
        public ?string $phone,
        public ?bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'license_number' => ['required', 'string', 'max:50', 'unique:doctors,license_number'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }
}
