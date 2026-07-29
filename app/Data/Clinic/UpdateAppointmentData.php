<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateAppointmentData extends Data
{
    public function __construct(
        public ?string $appointment_date,
        public ?string $appointment_time,
        public ?int $duration_minutes,
        public ?string $reason,
        public ?string $status,
        public ?string $cancellation_reason,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'appointment_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'appointment_time' => ['sometimes', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:10', 'max:120'],
            'reason' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'string', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])],
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => ($context->payload['status'] ?? null) === 'cancelled'),
            ],
        ];
    }
}
