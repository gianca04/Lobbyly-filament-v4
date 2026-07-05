<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('capacity')->default(1);
            $table->timestamps();
        });

        Schema::create('caracteristica_tipo_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_tipo_id')->constrained('room_types')->cascadeOnDelete();
            $table->foreignId('caracteristica_id')->constrained('features')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caracteristica_tipo_habitacion');
        Schema::dropIfExists('room_types');
    }
};
