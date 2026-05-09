<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->id();
            $table->morphs('authenticatable');
            // Identity information
            $table->string('type', 30); // type of identifier, such as email, phone, github, etc.
            $table->string('identifier', 200); // value of identifier, such as username, email, phone, github id, etc.
            $table->text('credentials')->nullable(); // password, token
            // Verification marker
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'identifier']);
            $table->unique(
                ['authenticatable_type', 'authenticatable_id', 'type', 'identifier'],
                'user_identities_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identities');
    }
};
