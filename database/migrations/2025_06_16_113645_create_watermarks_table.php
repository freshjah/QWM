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
        Schema::create('watermarks', function (Blueprint $table) {
            $table->id();
            $table->string('key_id')->default('2024-QMARK-V1');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->text('entropy_seed_encrypted');
            $table->string('timestamp');
            $table->text('signature');
            $table->string('signature_algorithm')->default('sha256'); //PQC (sphincs+, dilithium) will be used once python service is in
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watermarks');
    }
};
