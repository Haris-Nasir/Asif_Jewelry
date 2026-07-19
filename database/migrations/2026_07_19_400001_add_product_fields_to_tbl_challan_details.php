<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProductFieldsToTblChallanDetails extends Migration
{
    public function up()
    {
        Schema::table('tbl_challan_details', function (Blueprint $table) {
            $table->unsignedInteger('sell_quality_id')->nullable()->after('challan_type');
            $table->unsignedInteger('sell_category_id')->nullable()->after('sell_quality_id');
            $table->decimal('weight_grams', 12, 3)->nullable()->after('qty');
            $table->string('qty_unit', 20)->nullable()->after('weight_grams');
        });

        // Backfill: one detail row per challan gets master quality/weight when missing.
        $challans = DB::table('tbl_challan_msts')
            ->where('challan_mst_status', 1)
            ->select('challan_mst_id', 'sell_quality_id', 'challan_type', 'weight_grams', 'qty_unit', 'total_qty')
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
                if ($index === $lastIndex) {
                    $lineWeight = round($totalWeight - $allocated, 3);
                } else {
                    $lineWeight = $totalQty > 0
                        ? round($totalWeight * ($qty / $totalQty), 3)
                        : 0.0;
                    $allocated += $lineWeight;
                }

                DB::table('tbl_challan_details')
                    ->where('challan_details_id', $detail->challan_details_id)
                    ->update([
                        'sell_quality_id' => $challan->sell_quality_id,
                        'sell_category_id' => $challan->challan_type,
                        'weight_grams' => $lineWeight,
                        'qty_unit' => $challan->qty_unit ?: 'pcs',
                    ]);
            }
        }

        Schema::table('tbl_challan_details', function (Blueprint $table) {
            $table->foreign('sell_quality_id')
                ->references('sell_quality_id')
                ->on('tbl_sell_qualities')
                ->restrictOnDelete();
            $table->foreign('sell_category_id')
                ->references('sell_quality_category_id')
                ->on('tbl_sell_quality_categories')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table('tbl_challan_details', function (Blueprint $table) {
            $table->dropForeign(['sell_quality_id']);
            $table->dropForeign(['sell_category_id']);
            $table->dropColumn(['sell_quality_id', 'sell_category_id', 'weight_grams', 'qty_unit']);
        });
    }
}
