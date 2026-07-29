<?php

declare(strict_types=1);

namespace App\Data\Clinic;

use App\Models\DoctorSchedule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateDoctorScheduleData extends Data
{
    public function __construct(
        public int $day_of_week,
        public string $start_time,
        public string $end_time,
        public int $slot_duration_minutes,
        public ?bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration_minutes' => ['required', 'integer', 'min:10', 'max:120'],
            'is_active' => ['boolean'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $doctorId = request()->route('doctor')?->id;
            $scheduleId = request()->route('schedule')?->id;

            if ($doctorId && isset($data['day_of_week']) && isset($data['start_time'])) {
                $exists = DoctorSchedule::where('doctor_id', $doctorId)
                    ->where('day_of_week', $data['day_of_week'])
                    ->where('start_time', $data['start_time'])
                    ->where('id', '!=', $scheduleId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'start_time',
                        'Este médico ya tiene un horario en este día y hora.'
                    );
                }
            }
        });
    }
}
