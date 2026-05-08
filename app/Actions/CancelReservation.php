<?php

namespace App\Actions;

use App\Models\Erreserba;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CancelReservation
{
    public static function run(Erreserba $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            foreach (['eskariak', 'eskaariak'] as $ordersTable) {
                if (! Schema::hasTable($ordersTable) || ! Schema::hasColumn($ordersTable, 'erreserbak_id') || ! Schema::hasColumn($ordersTable, 'id')) {
                    continue;
                }

                $orderIds = DB::table($ordersTable)
                    ->where('erreserbak_id', $reservation->id)
                    ->pluck('id')
                    ->filter(fn ($id) => $id !== null)
                    ->values()
                    ->all();

                if (! $orderIds) {
                    continue;
                }

                foreach (['eskariak_has_produktuak', 'eskaariak_has_produktuak'] as $pivotTable) {
                    if (! Schema::hasTable($pivotTable)) {
                        continue;
                    }

                    foreach (['eskariak_id', 'eskaariak_id'] as $pivotFk) {
                        if (! Schema::hasColumn($pivotTable, $pivotFk)) {
                            continue;
                        }

                        DB::table($pivotTable)->whereIn($pivotFk, $orderIds)->delete();
                    }
                }

                DB::table($ordersTable)->whereIn('id', $orderIds)->delete();
            }

            if (DB::getDriverName() === 'mysql') {
                self::deleteForeignDependants($reservation);
            } elseif (Schema::hasTable('eskariak') && Schema::hasColumn('eskariak', 'erreserbak_id')) {
                DB::table('eskariak')->where('erreserbak_id', $reservation->id)->delete();
            }

            DB::table('erreserbak')
                ->where('id', $reservation->id)
                ->delete();
        }, 3);
    }

    protected static function deleteForeignDependants(Erreserba $reservation): void
    {
        $dbName = DB::connection()->getDatabaseName();

        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select(['TABLE_NAME', 'CONSTRAINT_NAME', 'COLUMN_NAME', 'REFERENCED_COLUMN_NAME'])
            ->where('REFERENCED_TABLE_SCHEMA', $dbName)
            ->where('REFERENCED_TABLE_NAME', 'erreserbak')
            ->whereNotNull('REFERENCED_COLUMN_NAME')
            ->get();

        $groups = [];
        foreach ($refs as $ref) {
            $table = (string) ($ref->TABLE_NAME ?? '');
            $constraint = (string) ($ref->CONSTRAINT_NAME ?? '');
            $column = (string) ($ref->COLUMN_NAME ?? '');
            $refColumn = (string) ($ref->REFERENCED_COLUMN_NAME ?? '');

            if ($table === '' || $constraint === '' || $column === '' || $refColumn === '' || $table === 'erreserbak') {
                continue;
            }

            $key = $table.'|'.$constraint;
            $groups[$key]['table'] = $table;
            $groups[$key]['pairs'][] = [$column, $refColumn];
        }

        foreach ($groups as $group) {
            $table = (string) ($group['table'] ?? '');
            $pairs = (array) ($group['pairs'] ?? []);

            if ($table === '' || ! Schema::hasTable($table) || ! $pairs) {
                continue;
            }

            $query = DB::table($table);
            $hasWhere = false;

            foreach ($pairs as $pair) {
                [$column, $refColumn] = $pair;

                if (! is_string($column) || ! is_string($refColumn) || ! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $value = $reservation->getAttribute($refColumn);
                if ($value === null) {
                    continue;
                }

                $query->where($column, $value);
                $hasWhere = true;
            }

            if ($hasWhere) {
                $query->delete();
            }
        }
    }
}
