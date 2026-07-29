<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateAppointmentNoteData extends Data
{
    public function __construct(
        public string $content,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
        ];
    }
}
