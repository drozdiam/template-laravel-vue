<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();

            $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('id');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->string('base_fiat', 3)->default('USD')->after('telegram_username');
            $table->text('bybit_api_key')->nullable()->after('base_fiat');
            $table->text('bybit_api_secret')->nullable()->after('bybit_api_key');
            $table->timestamp('bybit_connected_at')->nullable()->after('bybit_api_secret');
            $table->boolean('is_admin')->default(false)->after('bybit_connected_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_id',
                'telegram_username',
                'base_fiat',
                'bybit_api_key',
                'bybit_api_secret',
                'bybit_connected_at',
                'is_admin',
            ]);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};