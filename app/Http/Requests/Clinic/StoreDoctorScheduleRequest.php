<?php

namespace App\Http\Requests\Clinic;

use App\Models\DoctorSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration_minutes' => ['required', 'integer', 'min:10', 'max:120'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->doctor_id && $this->day_of_week !== null && $this->start_time) {
                $exists = DoctorSchedule::where('doctor_id', $this->doctor_id)
                    ->where('day_of_week', $this->day_of_week)
                    ->where('start_time', $this->start_time)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('start_time', 'Este médico ya tiene un horario en este día y hora.');
                }
            }
        });
    }
}
