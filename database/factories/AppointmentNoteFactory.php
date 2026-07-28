<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentNote>
 */
class AppointmentNoteFactory extends Factory
{
    protected $model = AppointmentNote::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'author_id' => User::factory()->doctor(),
            'content' => fake()->paragraph(),
        ];
    }

    public function forAppointment(Appointment $appointment): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_id' => $appointment->id,
        ]);
    }

    public function authoredBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
        ]);
    }
}
