<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO for transporting data for massive inventory input.
 *
 * Allows registering multiple items and their respective locations in a single transaction.
 *
 * @property-read int $userId Identifier of the responsible user.
 * @property-read array<int, array{item_id: int, location_id: int, quantity: float}> $items List of items to be entered.
 * @property-read string|null $notes General notes for the input transaction.
 */
readonly class InputMovementData
{
    /**
     * Create a new instance of the massive input DTO.
     *
     * @param  int  $userId  Identifier of the responsible user.
     * @param  array<int, array{item_id: int, location_id: int, quantity: float}>  $items  List of items with their locations and quantities.
     * @param  string|null  $notes  General notes for the transaction.
     */
    public function __construct(
        public int $userId,
        public array $items,
        public ?string $notes = null,
    ) {}

    /**
     * Calculates the total quantity across all items and distributions.
     */
    public function totalQuantity(): float
    {
        return array_sum(array_column($this->items, 'quantity'));
    }
}
