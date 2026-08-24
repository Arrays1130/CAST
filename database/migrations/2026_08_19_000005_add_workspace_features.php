<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->after('submitted_at');
            $table->string('tags')->nullable()->after('due_at');
            $table->unsignedTinyInteger('score')->nullable()->after('tags');
            $table->text('remarks')->nullable()->after('score');
            $table->timestamp('archived_at')->nullable()->after('remarks');
        });

        Schema::create('paper_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('drive_url')->nullable();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->timestamps();
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paper_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('paper_versions');
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn(['due_at', 'tags', 'score', 'remarks', 'archived_at']);
        });
    }
};
