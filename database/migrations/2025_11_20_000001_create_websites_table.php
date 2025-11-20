<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('domain')->unique();
            $table->string('root_path');
            $table->string('php_version')->nullable();
            $table->boolean('ssl_enabled')->default(false);
            $table->enum('ssl_status', ['none', 'pending', 'active', 'failed'])->default('none');
            $table->string('nginx_config_path')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};

