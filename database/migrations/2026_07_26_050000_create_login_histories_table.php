<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Login history supporting the auditing requirement of
 * 17_Authentication_Module.md §23.
 *
 * Log tables intentionally omit soft deletes (07A_Database_Standards.md §27).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('email')->nullable();
            $table->string('status', 32);
            $table->string('ip_address', 45)->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('device')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();

            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
