<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Fortify;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('erabiltzaileak')) {
            return;
        }

        $hasSecret = Schema::hasColumn('erabiltzaileak', 'two_factor_secret');
        $hasRecovery = Schema::hasColumn('erabiltzaileak', 'two_factor_recovery_codes');
        $hasConfirmedAt = Schema::hasColumn('erabiltzaileak', 'two_factor_confirmed_at');

        if ($hasSecret && $hasRecovery && (! Fortify::confirmsTwoFactorAuthentication() || $hasConfirmedAt)) {
            return;
        }

        Schema::table('erabiltzaileak', function (Blueprint $table) use ($hasSecret, $hasRecovery, $hasConfirmedAt) {
            if (! $hasSecret) {
                $table->text('two_factor_secret')->nullable();
            }

            if (! $hasRecovery) {
                $table->text('two_factor_recovery_codes')->nullable();
            }

            if (Fortify::confirmsTwoFactorAuthentication() && ! $hasConfirmedAt) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('erabiltzaileak')) {
            return;
        }

        $columns = [];
        if (Schema::hasColumn('erabiltzaileak', 'two_factor_secret')) {
            $columns[] = 'two_factor_secret';
        }
        if (Schema::hasColumn('erabiltzaileak', 'two_factor_recovery_codes')) {
            $columns[] = 'two_factor_recovery_codes';
        }
        if (Schema::hasColumn('erabiltzaileak', 'two_factor_confirmed_at')) {
            $columns[] = 'two_factor_confirmed_at';
        }

        if (! $columns) {
            return;
        }

        Schema::table('erabiltzaileak', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
