<?php

namespace Sinevia\Shop\Models;

class Order extends BaseModel {

    const STATUS_DRAFT = "Draft";
    const STATUS_PENDING_PAYMENT = "PendingPayment";
    const STATUS_PROCESSING = "Processing";
    const STATUS_ON_HOLD = "OnHold";
    const STATUS_COMPLETED = "Completed";
    const STATUS_CANCELLED = "Cancelled";
    const STATUS_DELETED = "Deleted";

    //protected $connection = 'sinevia';
    protected $table = 'snv_shops_order';
    protected $primaryKey = 'Id';
    public $timestamps = true;
    public $incrementing = false;
    public $useMicroid = false;
    public static $statusList = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING_PAYMENT => 'Pending Payment',
        self::STATUS_PROCESSING => "Processing",
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public static function tableCreate() {
        $o = new static;

        if (\Schema::connection($o->connection)->hasTable($o->table) == false) {
            return \Schema::connection($o->connection)->create($o->table, function (\Illuminate\Database\Schema\Blueprint $table) use ($o) {
                        $table->engine = 'InnoDB';
                        $table->string($o->primaryKey, 40)->primary();
                        $table->enum('Status', array_keys(self::$statusList))->default(self::STATUS_DRAFT);
                        $table->double('TotalPrice')->default(0.00);
                        $table->datetime('CreatedAt')->nullable();
                        $table->datetime('UpdatedAt')->nullable();
                        $table->datetime('DeletedAt')->nullable();
                    });
        }

        return true;
    }

    public static function tableDelete() {
        $o = new static;
        return \Schema::connection($o->connection)->dropIfExists($o->table);
    }

}
