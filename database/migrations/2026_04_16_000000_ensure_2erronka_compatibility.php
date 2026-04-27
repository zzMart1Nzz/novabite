<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('erabiltzaileak')) {
            $needsEmailVerifiedAt = ! Schema::hasColumn('erabiltzaileak', 'email_verified_at');
            $needsRememberToken = ! Schema::hasColumn('erabiltzaileak', 'remember_token');
            $needsTelefonoa = ! Schema::hasColumn('erabiltzaileak', 'telefonoa');

            if ($needsEmailVerifiedAt || $needsRememberToken || $needsTelefonoa) {
                Schema::table('erabiltzaileak', function (Blueprint $table) use ($needsEmailVerifiedAt, $needsRememberToken, $needsTelefonoa) {
                    if ($needsEmailVerifiedAt) {
                        $table->timestamp('email_verified_at')->nullable();
                    }

                    if ($needsRememberToken) {
                        $table->rememberToken();
                    }

                    if ($needsTelefonoa) {
                        $table->string('telefonoa', 45)->nullable();
                    }
                });
            }
        }

        if (! Schema::hasTable('erreserbak')) {
            $driver = DB::getDriverName();

            Schema::create('erreserbak', function (Blueprint $table) use ($driver) {
                if ($driver === 'sqlite') {
                    $table->integer('id');
                } else {
                    $table->integer('id')->autoIncrement();
                    $table->unique('id');
                }

                $table->string('bezero_izena', 45);
                $table->string('telefonoa', 45);
                $table->unsignedInteger('pertsona_kopurua');
                $table->dateTime('eguna_ordua');
                $table->double('prezio_totala')->default(0);
                $table->boolean('ordainduta')->default(false);
                $table->string('faktura_ruta', 255)->nullable();
                $table->integer('langileak_id');
                $table->integer('mahaiak_id');

                $table->primary(['id', 'langileak_id', 'mahaiak_id']);

                if (Schema::hasTable('langileak')) {
                    $table->foreign('langileak_id')->references('id')->on('langileak');
                }

                if (Schema::hasTable('mahaiak')) {
                    $table->foreign('mahaiak_id')->references('id')->on('mahaiak');
                }
            });
        }
    }

    public function down(): void {}
};
