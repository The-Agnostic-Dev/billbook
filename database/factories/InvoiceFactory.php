<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'number' => 'INV-'.fake()->unique()->numerify('9###'),
            'issue_date' => today()->subDays(10),
            'due_date' => today()->addDays(4),
            'sent_at' => null,
            'notes' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['sent_at' => now()->subDays(9)]);
    }

    /**
     * @param  array<int, array{0: string, 1: float|int, 2: float|int, 3?: float|int}>  $lines
     */
    public function withLines(array $lines): static
    {
        return $this->afterCreating(function ($invoice) use ($lines) {
            $invoice->syncItems(array_map(fn (array $line) => [
                'description' => $line[0],
                'quantity' => $line[1],
                'unit_price' => $line[2],
                'discount_percent' => $line[3] ?? 0,
            ], $lines));
            $invoice->refresh();
        });
    }
}
