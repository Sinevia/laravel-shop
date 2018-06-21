<?php

namespace Sinevia\Shop\Models;

class Product extends BaseModel {

    const STATUS_DRAFT = "Draft";
    const STATUS_PUBLISHED = "Published";
    const STATUS_UNPUBLISHED = "Unpublished";

    protected $table = 'snv_shops_product';
    protected $primaryKey = 'Id';
    public $timestamps = true;
    public $incrementing = false;
    public $useMicroId = true;

    public static function tableCreate() {
        $o = new Product;

        if (\Schema::connection($o->connection)->hasTable($o->table) == false) {
            return \Schema::connection($o->connection)->create($o->table, function (\Illuminate\Database\Schema\Blueprint $table) use ($o) {
                        $table->engine = 'InnoDB';
                        $table->string($o->primaryKey, 40)->primary();
                        $table->enum('Status',[self::STATUS_DRAFT,self::STATUS_PUBLISHED,self::STATUS_UNPUBLISHED])->default(self::STATUS_DRAFT);
                        $table->string('CategoryId', 40)->index();
                        $table->string('Title', 255);
                        $table->text('Summary')->nullable()->default(NULL);
                        $table->text('Description')->nullable()->default(NULL);
                        $table->double('Price')->default(0.00);
                        $table->integer('Quantity')->default(0);
                        $table->enum('IsFeatured', ['Yes', 'No'])->default('No');
                        $table->enum('ShowFrontPage', ['Yes', 'No'])->default('No');
                        $table->string('Sku', 40)->nullable()->default(NULL);
                        $table->datetime('CreatedAt')->nullable()->default(NULL);
                        $table->datetime('UpdatedAt')->nullable()->default(NULL);
                        $table->datetime('DeletedAt')->nullable()->default(NULL);
                    });
        }

        return true;
    }

    public static function tableDelete() {
        $o = new Product;
        return \Schema::connection($o->connection)->drop($o->table);
    }

}
