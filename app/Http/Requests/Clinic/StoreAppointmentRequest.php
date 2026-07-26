<?php

namespace App\Http\Requests\Clinic;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->doctor_id && $this->appointment_date && $this->appointment_time) {
                // Check unique constraint
                $exists = Appointment::where('doctor_id', $this->doctor_id)
                    ->where('appointment_date', $this->appointment_date)
                    ->where('appointment_time', $this->appointment_time)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('appointment_time', 'Este médico ya tiene un turno en esta fecha y hora.');
                }

                // Check schedule availability
                $dayOfWeek = Carbon::parse($this->appointment_date)->dayOfWeek;
                $hasSchedule = DoctorSchedule::where('doctor_id', $this->doctor_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('start_time', '<=', $this->appointment_time)
                    ->where('end_time', '>', $this->appointment_time)
                    ->active()
                    ->exists();

                if (! $hasSchedule) {
                    $validator->errors()->add('appointment_time', 'Este médico no tiene disponibilidad en este horario.');
                }
            }
        });
    }
}
