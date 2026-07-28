<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AppointmentNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $appointment_id
 * @property int $author_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AppointmentNote extends Model
{
    /** @use HasFactory<AppointmentNoteFactory> */
    use HasFactory;

    protected $fillable = ['appointment_id', 'author_id', 'content'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
