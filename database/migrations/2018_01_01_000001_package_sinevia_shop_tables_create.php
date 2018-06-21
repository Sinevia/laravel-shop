<?php

class PackageSineviaShopTablesCreate extends Illuminate\Database\Migrations\Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Sinevia\Shop\Models\Category::tableCreate();
        Sinevia\Shop\Models\Product::tableCreate();
        Sinevia\Shop\Models\Order::tableCreate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Sinevia\Shop\Models\Order::tableDelete();
        Sinevia\Shop\Models\Product::tableDelete();
        Sinevia\Shop\Models\Category::tableDelete();
    }

}
