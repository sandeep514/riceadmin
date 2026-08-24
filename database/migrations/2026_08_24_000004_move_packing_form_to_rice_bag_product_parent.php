<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One product has one packing form; multiple variants live in packing_sizes.
 */
class MovePackingFormToRiceBagProductParent extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form_id')) {
                $table->unsignedBigInteger('packing_form_id')->nullable()->after('additional_information');
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form')) {
                $table->string('packing_form', 64)->nullable()->after('packing_form_id');
            }
        });

        if (Schema::hasTable('web_rice_bag_product_packing_forms')) {
            $forms = DB::table('web_rice_bag_product_packing_forms')
                ->orderBy('id')
                ->get()
                ->groupBy('product_id');

            foreach ($forms as $productId => $rows) {
                $first = $rows->first();
                if ($first === null) {
                    continue;
                }
                DB::table('web_rice_bag_products')
                    ->where('id', $productId)
                    ->update([
                        'packing_form_id' => $first->packing_form_id,
                        'packing_form' => $first->packing_form,
                    ]);
            }

            Schema::dropIfExists('web_rice_bag_product_packing_forms');
        }
    }

    public function down()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        if (! Schema::hasTable('web_rice_bag_product_packing_forms')) {
            Schema::create('web_rice_bag_product_packing_forms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('packing_form_id')->nullable();
                $table->string('packing_form', 64)->nullable();
                $table->timestamps();

                $table->index('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_rice_bag_products')
                    ->onDelete('cascade');
            });
        }

        $products = DB::table('web_rice_bag_products')
            ->whereNotNull('packing_form_id')
            ->orWhereNotNull('packing_form')
            ->get(['id', 'packing_form_id', 'packing_form']);

        foreach ($products as $product) {
            DB::table('web_rice_bag_product_packing_forms')->insert([
                'product_id' => $product->id,
                'packing_form_id' => $product->packing_form_id,
                'packing_form' => $product->packing_form,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (Schema::hasColumn('web_rice_bag_products', 'packing_form_id')) {
                $table->dropColumn('packing_form_id');
            }
            if (Schema::hasColumn('web_rice_bag_products', 'packing_form')) {
                $table->dropColumn('packing_form');
            }
        });
    }
}
