<?php

namespace Tests\Feature;

use App\Filament\Resources\Floors\Pages\ListFloors;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FloorResourceTest extends TestCase
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

    public function test_can_render_floor_resource_list_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->assertSuccessful();
    }

    public function test_can_list_floors(): void
    {
        $floor = Floor::factory()->create([
            'name' => 'Piso 1',
            'description' => 'Primer piso',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->assertCanSeeTableRecords([$floor])
            ->assertCanRenderTableColumn('name');
    }

    public function test_can_create_floor(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->callAction('create', [
                'name' => 'Piso 2',
                'description' => 'Segundo piso',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('floors', [
            'name' => 'Piso 2',
            'description' => 'Segundo piso',
        ]);
    }

    public function test_can_validate_floor_creation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->callAction('create', [
                'name' => '',
            ])
            ->assertHasActionErrors(['name']);
    }

    public function test_can_edit_floor(): void
    {
        $floor = Floor::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->callTableAction('edit', $floor, [
                'name' => 'Piso Modificado',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('floors', [
            'id' => $floor->id,
            'name' => 'Piso Modificado',
        ]);
    }

    public function test_can_delete_floor(): void
    {
        $floor = Floor::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListFloors::class)
            ->callTableAction('delete', $floor)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($floor);
    }
}
