<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent upgrade for environments that already ran the first
 * web_rice_bag_products migration when packing fields lived on the parent.
 */
class SplitRiceBagProductPackingSizes extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        if (! Schema::hasTable('web_rice_bag_product_packing_sizes')) {
            Schema::create('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('packing_id')->nullable();
                $table->string('packing', 255)->nullable();
                $table->string('packing_form', 64)->nullable();
                $table->decimal('available_quantity', 12, 2)->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('product_id');
                $table->index('packing_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_rice_bag_products')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('web_rice_bag_products', 'packing_id')) {
            $products = DB::table('web_rice_bag_products')->get([
                'id',
                'packing_id',
                'packing',
                'packing_form',
                'available_quantity',
                'price',
            ]);

            foreach ($products as $product) {
                $already = DB::table('web_rice_bag_product_packing_sizes')
                    ->where('product_id', $product->id)
                    ->exists();
                if ($already) {
                    continue;
                }

                if ($product->packing_id === null
                    && ($product->packing === null || $product->packing === '')
                    && ($product->packing_form === null || $product->packing_form === '')
                    && $product->available_quantity === null
                    && $product->price === null
                ) {
                    continue;
                }

                DB::table('web_rice_bag_product_packing_sizes')->insert([
                    'product_id' => $product->id,
                    'packing_id' => $product->packing_id,
                    'packing' => $product->packing,
                    'packing_form' => $product->packing_form,
                    'available_quantity' => $product->available_quantity,
                    'price' => $product->price,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('web_rice_bag_products', function (Blueprint $table) {
                if (Schema::hasColumn('web_rice_bag_products', 'packing_id')) {
                    $table->dropColumn([
                        'packing_id',
                        'packing',
                        'packing_form',
                        'available_quantity',
                        'price',
                    ]);
                }
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_id')) {
                $table->unsignedBigInteger('packing_id')->nullable()->after('rice_form');
                $table->string('packing', 255)->nullable()->after('packing_id');
                $table->string('packing_form', 64)->nullable()->after('packing');
                $table->decimal('available_quantity', 12, 2)->nullable()->after('print_type');
                $table->decimal('price', 12, 2)->nullable()->after('available_quantity');
            }
        });

        if (Schema::hasTable('web_rice_bag_product_packing_sizes')) {
            $sizes = DB::table('web_rice_bag_product_packing_sizes')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $seen = [];
            foreach ($sizes as $size) {
                if (isset($seen[$size->product_id])) {
                    continue;
                }
                $seen[$size->product_id] = true;
                DB::table('web_rice_bag_products')->where('id', $size->product_id)->update([
                    'packing_id' => $size->packing_id,
                    'packing' => $size->packing,
                    'packing_form' => $size->packing_form,
                    'available_quantity' => $size->available_quantity,
                    'price' => $size->price,
                ]);
            }

            Schema::dropIfExists('web_rice_bag_product_packing_sizes');
        }
    }
}
