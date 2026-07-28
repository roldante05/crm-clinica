<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $doctor_id
 * @property int $patient_id
 * @property string $appointment_date
 * @property string $appointment_time
 * @property int $duration_minutes
 * @property AppointmentStatus $status
 * @property string|null $reason
 * @property string|null $cancellation_reason
 * @property Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property Carbon|null $confirmed_at
 * @property int|null $confirmed_by
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'status',
        'reason',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'confirmed_at',
        'confirmed_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'duration_minutes' => 'integer',
            'status' => AppointmentStatus::class,
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AppointmentNote::class);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if ($this->status instanceof AppointmentStatus) {
            $array['status'] = [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ];
        }

        return $array;
    }

    /**
     * Mark as confirmed.
     */
    public function confirm(int $userId): bool
    {
        return $this->update([
            'status' => AppointmentStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
        ]);
    }

    /**
     * Mark as completed.
     */
    public function complete(): bool
    {
        return $this->update(['status' => AppointmentStatus::Completed]);
    }

    /**
     * Cancel with reason.
     */
    public function cancel(string $reason, int $userId): bool
    {
        return $this->update([
            'status' => AppointmentStatus::Cancelled,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
        ]);
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(): bool
    {
        return $this->update(['status' => AppointmentStatus::NoShow]);
    }

    // --- Scopes ---

    public function scopeForDate($query, $date)
    {
        return $query->where('appointment_date', $date);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled,
                AppointmentStatus::Completed,
                AppointmentStatus::NoShow,
            ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    public function scopeWithStatus($query, AppointmentStatus $status)
    {
        return $query->where('status', $status);
    }
}
