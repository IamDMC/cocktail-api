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
        Schema::create('cocktail_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('step_number');
            $table->text('instruction');

            $table->foreignId('cocktail_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['cocktail_id', 'step_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cocktail_steps');
    }
};
