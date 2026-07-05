<?php

namespace Tests\Unit;

use App\Enums\RoomStatus;
use PHPUnit\Framework\TestCase;

class RoomStatusTest extends TestCase
{
    public function test_room_status_enum_has_expected_cases(): void
    {
        $this->assertSame('out_of_service', RoomStatus::OUT_OF_SERVICE->value);
        $this->assertSame('available', RoomStatus::AVAILABLE->value);
        $this->assertSame('cleaning', RoomStatus::CLEANING->value);
        $this->assertSame('reserved', RoomStatus::RESERVED->value);
    }

    public function test_room_status_enum_returns_correct_labels(): void
    {
        $this->assertSame('Inhabilitada', RoomStatus::OUT_OF_SERVICE->label());
        $this->assertSame('Disponible', RoomStatus::AVAILABLE->label());
        $this->assertSame('Limpieza', RoomStatus::CLEANING->label());
        $this->assertSame('Reservada', RoomStatus::RESERVED->label());
    }

    public function test_room_status_enum_returns_array(): void
    {
        $expected = [
            'OUT_OF_SERVICE' => 'out_of_service',
            'AVAILABLE' => 'available',
            'CLEANING' => 'cleaning',
            'RESERVED' => 'reserved',
        ];

        $this->assertSame($expected, RoomStatus::array());
    }
}
