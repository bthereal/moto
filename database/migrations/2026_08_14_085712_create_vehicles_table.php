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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('chassis')->comment('Chassis designation, e.g. RB20');
            $table->string('engine_supplier');
            $table->unsignedTinyInteger('car_number');
            $table->unsignedSmallInteger('season_year');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['season_year', 'car_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
