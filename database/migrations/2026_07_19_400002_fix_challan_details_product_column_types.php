<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixChallanDetailsProductColumnTypes extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tbl_challan_details', 'sell_quality_id')) {
            return;
        }

        // Drop FKs if partial apply left them; ignore errors.
        try {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->dropForeign(['sell_quality_id']);
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->dropForeign(['sell_category_id']);
            });
        } catch (\Throwable $e) {
        }

        // Match referenced PK types (int unsigned).
        try {
            DB::statement('ALTER TABLE tbl_challan_details MODIFY sell_quality_id INT UNSIGNED NULL');
            DB::statement('ALTER TABLE tbl_challan_details MODIFY sell_category_id INT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }

        // Backfill any remaining nulls from master.
        $challans = DB::table('tbl_challan_msts')
            ->where('challan_mst_status', 1)
            ->select('challan_mst_id', 'sell_quality_id', 'challan_type', 'weight_grams', 'qty_unit')
            ->get();

        foreach ($challans as $challan) {
            $details = DB::table('tbl_challan_details')
                ->where('challan_mst_id', $challan->challan_mst_id)
                ->where('challan_details_status', 1)
                ->orderBy('no')
                ->get();

            if ($details->isEmpty()) {
                continue;
            }

            $totalQty = (float) $details->sum('qty');
            $totalWeight = (float) $challan->weight_grams;
            $allocated = 0.0;
            $lastIndex = $details->count() - 1;

            foreach ($details as $index => $detail) {
                $qty = (float) $detail->qty;
                $lineWeight = $detail->weight_grams;
                if ($lineWeight === null) {
                    if ($index === $lastIndex) {
                        $lineWeight = round($totalWeight - $allocated, 3);
                    } else {
                        $lineWeight = $totalQty > 0
                            ? round($totalWeight * ($qty / $totalQty), 3)
                            : 0.0;
                        $allocated += $lineWeight;
                    }
                }

                DB::table('tbl_challan_details')
                    ->where('challan_details_id', $detail->challan_details_id)
                    ->update([
                        'sell_quality_id' => $detail->sell_quality_id ?: $challan->sell_quality_id,
                        'sell_category_id' => $detail->sell_category_id ?: $challan->challan_type,
                        'weight_grams' => $lineWeight,
                        'qty_unit' => $detail->qty_unit ?: ($challan->qty_unit ?: 'pcs'),
                    ]);
            }
        }

        try {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->foreign('sell_quality_id')
                    ->references('sell_quality_id')
                    ->on('tbl_sell_qualities')
                    ->restrictOnDelete();
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->foreign('sell_category_id')
                    ->references('sell_quality_category_id')
                    ->on('tbl_sell_quality_categories')
                    ->restrictOnDelete();
            });
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        Schema::table('tbl_challan_details', function (Blueprint $table) {
            $table->dropForeign(['sell_quality_id']);
            $table->dropForeign(['sell_category_id']);
        });
    }
}
