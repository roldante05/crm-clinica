<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programado',
            self::Confirmed => 'Confirmado',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
            self::NoShow => 'No asistió',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'blue',
            self::Confirmed => 'green',
            self::Completed => 'gray',
            self::Cancelled => 'red',
            self::NoShow => 'orange',
        };
    }
}
