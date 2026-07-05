<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Models\Feature;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    }

    public function test_can_render_room_resource_list_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->assertSuccessful();
    }

    public function test_can_list_rooms(): void
    {
        $room = Room::factory()->create([
            'number' => '101',
            'status' => RoomStatus::AVAILABLE,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->assertCanSeeTableRecords([$room])
            ->assertCanRenderTableColumn('number')
            ->assertCanRenderTableColumn('status');
    }

    public function test_can_create_room(): void
    {
        $floor = Floor::factory()->create();
        $roomType = RoomType::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->callAction('create', [
                'number' => '202',
                'status' => RoomStatus::CLEANING,
                'floor_id' => $floor->id,
                'room_type_id' => $roomType->id,
                'location' => 'Bloque A',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('rooms', [
            'number' => '202',
            'status' => RoomStatus::CLEANING->value,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'location' => 'Bloque A',
        ]);
    }

    public function test_can_validate_room_creation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->callAction('create', [
                'number' => '',
                'floor_id' => '',
            ])
            ->assertHasActionErrors(['number', 'floor_id']);
    }

    public function test_can_edit_room(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->callTableAction('edit', $room, [
                'number' => '404-EDIT',
                'location' => 'Ubicación editada',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'number' => '404-EDIT',
            'location' => 'Ubicación editada',
        ]);
    }

    public function test_can_delete_room(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListRooms::class)
            ->callTableAction('delete', $room)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($room);
    }

    public function test_get_precio_returns_room_type_final_price(): void
    {
        $roomType = RoomType::factory()->create(['base_price' => 150.00]);
        $feature = Feature::factory()->create(['price' => 25.00]);
        $roomType->features()->attach($feature->id);

        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertEquals(175.00, $room->getPrecio());
    }

    public function test_cambiar_estado_updates_status_and_logs(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::AVAILABLE]);

        $this->assertTrue($room->cambiarEstado(RoomStatus::CLEANING, $this->user->id, 'Limpieza rutinaria'));
        $this->assertEquals(RoomStatus::CLEANING, $room->fresh()->status);

        $this->assertDatabaseHas('room_status_logs', [
            'room_id' => $room->id,
            'user_id' => $this->user->id,
            'status' => RoomStatus::CLEANING->value,
            'note' => 'Limpieza rutinaria',
        ]);

        $this->assertCount(1, $room->statusLogs);
    }

    public function test_get_caracteristicas_removibles_returns_only_removable_features(): void
    {
        $roomType = RoomType::factory()->create();
        $removableFeature = Feature::factory()->create(['is_removable' => true]);
        $nonRemovableFeature = Feature::factory()->create(['is_removable' => false]);

        $roomType->features()->attach([$removableFeature->id, $nonRemovableFeature->id]);

        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $removables = $room->getCaracteristicasRemovibles();

        $this->assertTrue($removables->contains($removableFeature));
        $this->assertFalse($removables->contains($nonRemovableFeature));
    }
}
