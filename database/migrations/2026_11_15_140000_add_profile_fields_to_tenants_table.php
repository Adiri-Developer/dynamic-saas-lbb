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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('domain');
            $table->text('address')->nullable()->after('phone');
            $table->text('description')->nullable()->after('address');
            $table->string('logo_path')->nullable()->after('description');
            $table->string('primary_color', 30)->nullable()->default('#2563eb')->after('logo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'description',
                'logo_path',
                'primary_color',
            ]);
        });
    }
};
