<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdatePatientData extends Data
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        #[Unique('patients', 'email', ignore: new RouteParameterReference('patient'))]
        public ?string $email,
        public string $phone,
        public ?string $secondary_phone,
        public ?string $document_type,
        #[Unique('patients', 'document_number', ignore: new RouteParameterReference('patient'))]
        public ?string $document_number,
        public ?string $date_of_birth,
        public ?string $gender,
        public ?string $address,
        public ?string $city,
        public ?string $notes,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'secondary_phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['nullable', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other,unspecified'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
