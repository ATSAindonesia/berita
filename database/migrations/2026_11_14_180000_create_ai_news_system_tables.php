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
        // 1. search_sessions table
        Schema::create('search_sessions', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->string('status', 32)->default('pending')->index();
            $table->json('search_queries')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 2. news_sources table
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_session_id')
                ->nullable()
                ->constrained('search_sessions')
                ->nullOnDelete();
            $table->string('source_name')->nullable()->index();
            $table->text('title');
            $table->text('url');
            $table->text('canonical_url')->nullable();
            $table->string('domain')->nullable()->index();
            $table->text('snippet')->nullable();
            $table->longText('raw_content')->nullable();
            $table->longText('clean_content')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->float('relevance_score')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamps();
        });

        // 3. Update news_summaries with relations and structured fields
        Schema::table('news_summaries', function (Blueprint $table) {
            $table->foreignId('search_session_id')
                ->nullable()
                ->after('id')
                ->constrained('search_sessions')
                ->nullOnDelete();
            $table->json('key_points')->nullable()->after('summary');
            $table->text('chronology')->nullable()->after('key_points');
            $table->text('source_comparison')->nullable()->after('chronology');
            $table->text('unconfirmed_facts')->nullable()->after('source_comparison');
            $table->text('conclusion')->nullable()->after('unconfirmed_facts');
        });

        // 4. summary_sources (pivot relation table between summaries and news_sources)
        Schema::create('summary_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_summary_id')
                ->constrained('news_summaries')
                ->cascadeOnDelete();
            $table->foreignId('news_source_id')
                ->constrained('news_sources')
                ->cascadeOnDelete();
            $table->text('relevance_note')->nullable();
            $table->timestamps();

            $table->unique(['news_summary_id', 'news_source_id']);
        });

        // 5. ai_prompts table
        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_session_id')
                ->nullable()
                ->constrained('search_sessions')
                ->cascadeOnDelete();
            $table->string('prompt_type', 64)->index();
            $table->longText('system_prompt');
            $table->longText('user_prompt');
            $table->longText('ai_response')->nullable();
            $table->string('model', 64)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->string('status', 32)->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('summary_sources');

        Schema::table('news_summaries', function (Blueprint $table) {
            $table->dropForeign(['search_session_id']);
            $table->dropColumn([
                'search_session_id',
                'key_points',
                'chronology',
                'source_comparison',
                'unconfirmed_facts',
                'conclusion',
            ]);
        });

        Schema::dropIfExists('news_sources');
        Schema::dropIfExists('search_sessions');
    }
};
