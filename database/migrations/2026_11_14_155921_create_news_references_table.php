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
        Schema::create('news_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_summary_id')
                ->index()
                ->constrained('news_summaries')
                ->cascadeOnDelete();
            $table->string('source_name')->index();
            $table->string('source_title')->nullable();
            $table->text('source_url');
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_references');
    }
};
