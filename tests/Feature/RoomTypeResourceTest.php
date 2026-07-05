<?php

namespace Tests\Feature;

use App\Filament\Resources\RoomTypes\Pages\ListRoomTypes;
use App\Models\Feature;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomTypeResourceTest extends TestCase
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

    public function test_can_render_room_type_resource_list_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->assertSuccessful();
    }

    public function test_can_list_room_types(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Familiar',
            'base_price' => 150.00,
            'capacity' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->assertCanSeeTableRecords([$roomType])
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('base_price')
            ->assertCanRenderTableColumn('capacity');
    }

    public function test_price_accessors_calculate_correctly(): void
    {
        $roomType = RoomType::factory()->create([
            'base_price' => 100.00,
        ]);

        $feature1 = Feature::factory()->create(['price' => 20.00]);
        $feature2 = Feature::factory()->create(['price' => 15.50]);

        $roomType->features()->attach([$feature1->id, $feature2->id]);

        // Assert getPrecioCaracteristicas() and features_price attribute
        $this->assertEquals(35.50, $roomType->getPrecioCaracteristicas());
        $this->assertEquals(35.50, $roomType->features_price);

        // Assert getPrecioFinal() and final_price attribute
        $this->assertEquals(135.50, $roomType->getPrecioFinal());
        $this->assertEquals(135.50, $roomType->final_price);
    }

    public function test_can_create_room_type_with_features(): void
    {
        $feature1 = Feature::factory()->create(['name' => 'Jacuzzi', 'price' => 30.00]);
        $feature2 = Feature::factory()->create(['name' => 'Mini Bar', 'price' => 15.00]);

        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->callAction('create', [
                'name' => 'Premium Suite',
                'base_price' => 200.00,
                'capacity' => 2,
                'is_active' => true,
                'features' => [$feature1->id, $feature2->id],
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('room_types', [
            'name' => 'Premium Suite',
            'base_price' => 200.00,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $roomType = RoomType::where('name', 'Premium Suite')->first();
        $this->assertCount(2, $roomType->features);
        $this->assertEquals(45.00, $roomType->features_price);
        $this->assertEquals(245.00, $roomType->final_price);
    }

    public function test_can_validate_room_type_creation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->callAction('create', [
                'name' => '',
                'base_price' => 'not-numeric',
                'capacity' => 'not-numeric',
            ])
            ->assertHasActionErrors(['name', 'base_price', 'capacity']);
    }

    public function test_can_edit_room_type(): void
    {
        $roomType = RoomType::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->callTableAction('edit', $roomType, [
                'name' => 'Nombre Modificado Suite',
                'base_price' => 350.00,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('room_types', [
            'id' => $roomType->id,
            'name' => 'Nombre Modificado Suite',
            'base_price' => 350.00,
        ]);
    }

    public function test_can_delete_room_type(): void
    {
        $roomType = RoomType::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListRoomTypes::class)
            ->callTableAction('delete', $roomType)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($roomType);
    }
}
