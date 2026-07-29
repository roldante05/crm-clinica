<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateAppointmentData extends Data
{
    public function __construct(
        public int $doctor_id,
        public int $patient_id,
        public string $appointment_date,
        public string $appointment_time,
        public ?int $duration_minutes,
        public ?string $reason,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:10', 'max:120'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            if (! empty($data['doctor_id']) && ! empty($data['appointment_date']) && ! empty($data['appointment_time'])) {
                $exists = Appointment::where('doctor_id', $data['doctor_id'])
                    ->where('appointment_date', $data['appointment_date'])
                    ->where('appointment_time', $data['appointment_time'])
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'appointment_time',
                        'Este médico ya tiene un turno en esta fecha y hora.'
                    );
                }

                $dayOfWeek = Carbon::parse($data['appointment_date'])->dayOfWeek;
                $hasSchedule = DoctorSchedule::where('doctor_id', $data['doctor_id'])
                    ->where('day_of_week', $dayOfWeek)
                    ->where('start_time', '<=', $data['appointment_time'])
                    ->where('end_time', '>', $data['appointment_time'])
                    ->active()
                    ->exists();

                if (! $hasSchedule) {
                    $validator->errors()->add(
                        'appointment_time',
                        'Este médico no tiene disponibilidad en este horario.'
                    );
                }
            }
        });
    }
}
