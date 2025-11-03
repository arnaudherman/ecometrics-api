<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('badge_level', ['bronze', 'silver', 'gold', 'platinum']);
            $table->timestamp('issued_at');
            $table->timestamp('valid_until');
            $table->timestamps();
            
            // Index pour recherches rapides
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_certificates');
    }
};