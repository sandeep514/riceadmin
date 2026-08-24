<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reshape web_rice_bag_product_packing_sizes to match frontend payload.
 * Packing form lives on web_rice_bag_products (one form, many size variants).
 */
class ReshapeRiceBagProductsToPayload extends Migration
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
            Schema::dropIfExists('web_rice_bag_product_packing_forms');
        }

        if (! Schema::hasTable('web_rice_bag_product_packing_sizes')) {
            Schema::create('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('packing_size_id')->nullable();
                $table->string('packing_size', 255)->nullable();
                $table->decimal('rate', 12, 2)->nullable();
                $table->decimal('gst', 8, 2)->nullable();
                $table->decimal('total_price', 12, 2)->nullable();
                $table->string('bag_size', 255)->nullable();
                $table->string('bag_weight', 64)->nullable();
                $table->string('image', 255)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('product_id');
                $table->index('packing_size_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_rice_bag_products')
                    ->onDelete('cascade');
            });

            return;
        }

        Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_size_id')) {
                $table->unsignedBigInteger('packing_size_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_size')) {
                $table->string('packing_size', 255)->nullable()->after('packing_size_id');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'rate')) {
                $table->decimal('rate', 12, 2)->nullable()->after('packing_size');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'gst')) {
                $table->decimal('gst', 8, 2)->nullable()->after('rate');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'total_price')) {
                $table->decimal('total_price', 12, 2)->nullable()->after('gst');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'bag_size')) {
                $table->string('bag_size', 255)->nullable()->after('total_price');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'bag_weight')) {
                $table->string('bag_weight', 64)->nullable()->after('bag_size');
            }
            if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'image')) {
                $table->string('image', 255)->nullable()->after('bag_weight');
            }
        });

        $legacySizeColumns = ['packing_id', 'packing', 'packing_form', 'available_quantity', 'price'];
        $toDrop = [];
        foreach ($legacySizeColumns as $column) {
            if (Schema::hasColumn('web_rice_bag_product_packing_sizes', $column)) {
                $toDrop[] = $column;
            }
        }
        if (! empty($toDrop)) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        if (Schema::hasTable('web_rice_bag_product_images')) {
            Schema::dropIfExists('web_rice_bag_product_images');
        }
    }

    public function down()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form_id')) {
                $table->unsignedBigInteger('packing_form_id')->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form')) {
                $table->string('packing_form', 64)->nullable();
            }
        });

        Schema::dropIfExists('web_rice_bag_product_packing_forms');

        if (Schema::hasTable('web_rice_bag_product_packing_sizes')) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_id')) {
                    $table->unsignedBigInteger('packing_id')->nullable();
                }
                if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing')) {
                    $table->string('packing', 255)->nullable();
                }
                if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_form')) {
                    $table->string('packing_form', 64)->nullable();
                }
                if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'available_quantity')) {
                    $table->decimal('available_quantity', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'price')) {
                    $table->decimal('price', 12, 2)->nullable();
                }
            });

            $newColumns = [
                'packing_size_id',
                'packing_size',
                'rate',
                'gst',
                'total_price',
                'bag_size',
                'bag_weight',
                'image',
            ];
            $toDrop = [];
            foreach ($newColumns as $column) {
                if (Schema::hasColumn('web_rice_bag_product_packing_sizes', $column)) {
                    $toDrop[] = $column;
                }
            }
            if (! empty($toDrop)) {
                Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn($toDrop);
                });
            }
        }
    }
}
