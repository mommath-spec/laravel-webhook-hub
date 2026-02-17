<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->string('url');
            $table->string('secret')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('last_status', 20)->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->timestamps();

            $table->index(['endpoint_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_destinations');
    }
};
