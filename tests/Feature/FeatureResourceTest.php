<?php

namespace Tests\Feature;

use App\Filament\Resources\Features\Pages\ListFeatures;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureResourceTest extends TestCase
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

    public function test_can_render_feature_resource_list_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->assertSuccessful();
    }

    public function test_can_list_features(): void
    {
        $feature = Feature::factory()->create([
            'name' => 'Aire Acondicionado',
            'price' => 15.50,
            'is_active' => true,
            'is_removable' => false,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->assertCanSeeTableRecords([$feature])
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('price');
    }

    public function test_can_create_feature(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->callAction('create', [
                'name' => 'Jacuzzi',
                'price' => 50.00,
                'is_active' => true,
                'is_removable' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('features', [
            'name' => 'Jacuzzi',
            'price' => 50.00,
            'is_active' => true,
            'is_removable' => true,
        ]);
    }

    public function test_can_validate_feature_creation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->callAction('create', [
                'name' => '',
                'price' => 'not-numeric',
            ])
            ->assertHasActionErrors(['name', 'price']);
    }

    public function test_can_edit_feature(): void
    {
        $feature = Feature::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->callTableAction('edit', $feature, [
                'name' => 'Nombre Modificado',
                'price' => 120.00,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'name' => 'Nombre Modificado',
            'price' => 120.00,
        ]);
    }

    public function test_can_delete_feature(): void
    {
        $feature = Feature::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(ListFeatures::class)
            ->callTableAction('delete', $feature)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($feature);
    }

    public function test_scopes_filter_features_correctly(): void
    {
        // 1. Active & Removable
        $f1 = Feature::factory()->create(['is_active' => true, 'is_removable' => true]);
        // 2. Active & Not Removable
        $f2 = Feature::factory()->create(['is_active' => true, 'is_removable' => false]);
        // 3. Inactive & Removable
        $f3 = Feature::factory()->create(['is_active' => false, 'is_removable' => true]);

        // Verify scopeActivas / scopeActive
        $activeSpanish = Feature::activas()->get();
        $activeEnglish = Feature::active()->get();

        $this->assertTrue($activeSpanish->contains($f1));
        $this->assertTrue($activeSpanish->contains($f2));
        $this->assertFalse($activeSpanish->contains($f3));

        $this->assertTrue($activeEnglish->contains($f1));
        $this->assertTrue($activeEnglish->contains($f2));
        $this->assertFalse($activeEnglish->contains($f3));

        // Verify scopeRemovibles / scopeRemovable
        $removableSpanish = Feature::removibles()->get();
        $removableEnglish = Feature::removable()->get();

        $this->assertTrue($removableSpanish->contains($f1));
        $this->assertFalse($removableSpanish->contains($f2));
        $this->assertTrue($removableSpanish->contains($f3));

        $this->assertTrue($removableEnglish->contains($f1));
        $this->assertFalse($removableEnglish->contains($f2));
        $this->assertTrue($removableEnglish->contains($f3));
    }
}
