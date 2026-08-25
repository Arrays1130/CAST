<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->json('reference_scan')->nullable()->after('remarks');
            $table->timestamp('reference_scanned_at')->nullable()->after('reference_scan');
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn(['reference_scan', 'reference_scanned_at']);
        });
    }
};
