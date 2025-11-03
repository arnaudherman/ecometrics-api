<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->bigInteger('requests_count')->default(0);
            $table->decimal('storage_gb', 10, 2)->default(0);
            $table->decimal('cpu_hours', 10, 2)->default(0);
            $table->decimal('carbon_footprint_kg', 10, 3)->default(0);
            $table->timestamps();
            
            // Index composé pour requêtes optimisées
            $table->index(['application_id', 'date']);
            
            // Contrainte d'unicité : une seule métrique par app par jour
            $table->unique(['application_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};