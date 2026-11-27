<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pendiente',
            'total' => fake()->randomFloat(2, 10, 500),
        ];
    }

    /** Pedido en un estado concreto (pendiente, procesando, enviado, entregado, cancelado). */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
