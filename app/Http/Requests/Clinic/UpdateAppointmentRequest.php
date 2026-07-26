<?php

namespace App\Http\Requests\Clinic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'appointment_time' => ['sometimes', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:10', 'max:120'],
            'reason' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'string', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])],
            'cancellation_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:1000'],
        ];
    }
}
