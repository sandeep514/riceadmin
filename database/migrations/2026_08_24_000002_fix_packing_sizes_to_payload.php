<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * packingSizes payload columns only:
 * packingId, packing, packingForm, availableQuantity, price
 */
class FixPackingSizesToPayload extends Migration
{
    public function up()
    {
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
            });

            return;
        }

        Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_id')) {
                $table->unsignedBigInteger('packing_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing')) {
                $table->string('packing', 255)->nullable()->after('packing_id');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_form')) {
                $table->string('packing_form', 64)->nullable()->after('packing');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'available_quantity')) {
                $table->decimal('available_quantity', 12, 2)->nullable()->after('packing_form');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->after('available_quantity');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('price');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('web_rice_bag_product_packing_sizes')) {
            return;
        }

        if (Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_form')) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->dropColumn('packing_form');
            });
        }
    }
}
