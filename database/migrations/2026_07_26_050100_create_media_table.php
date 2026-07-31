<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Centralised media table backing the Media System (16_Media_System.md).
 *
 * Only file references are persisted, never binary contents
 * (07_Database_Architecture.md §26).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();

            $table->morphs('mediable');

            $table->string('collection')->default('default');
            $table->string('disk', 64);
            $table->string('directory');
            $table->string('filename');
            $table->string('original_name');
            $table->string('extension', 16);
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('hash', 64)->nullable();
            $table->json('conversions')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('collection');
            $table->index('mime_type');
            $table->index('hash');
            $table->index('created_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
