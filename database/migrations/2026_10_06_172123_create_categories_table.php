<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories')->nullOnDelete();

            // اطلاعات اصلی
            $table->string('name', 150);
            $table->string('slug', 180)->unique()->index();
            $table->text('description')->nullable();

            // تصاویر
            $table->string('image', 500)->nullable();
            $table->string('icon', 500)->nullable();
            $table->string('banner', 500)->nullable();
            $table->string('color', 20)->nullable();

            // SEO
            $table->string('meta_title', 200)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_title', 200)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->json('schema_markup')->nullable();
            $table->boolean('is_indexable')->default(true);
            $table->boolean('is_followable')->default(true);

            // نمایش
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'order']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};