<?php

namespace Sinevia\Shop\Models;

class Category extends BaseModel {

    const STATUS_PUBLISHED = "Published";
    const STATUS_UNPUBLISHED = "Unpublished";
    const STATUS_DELETED = "Deleted";

    protected $table = 'snv_shop_category';
    protected $primaryKey = 'Id';
    public $timestamps = true;
    public $incrementing = false;
    public $useMicroId = true;

    public function getChildren() {
        $result = self::where('ParentId', '=', $this->Id)
                ->orderBy('Position', 'ASC')
                ->get();
        if ($result == false) {
            return array();
        }
        return $result;
    }

    public function getParent() {
        $parent = self::find($this->ParentId);
        return $parent;
    }

    public function getPath() {
        $path = array();
        $path[] = $this;
        $parent = $this->getParent();
        if($parent!=null) {
            $path = array_merge($path, $parent->getPath());
        }
        return $path;
    }

    public function traverse() {
        $travsersed = array();
        $travsersed[] = $this;
        $children = $this->getChildren();
        foreach ($children as $child) {
            $travsersed = array_merge($travsersed, $child->traverse());
        }
        return $travsersed;
    }

    public static function traverseCategoryByCategoryId($category_id) {
        $category = self::getFaqCategoryById($category_id);
        $travsersed = array();
        $travsersed[] = $category;
        $children = self::getChildrenByCategoryId($category_id);
        foreach ($children as $child) {
            $travsersed = array_merge($travsersed, self::traverseCategoryByCategoryId($child['id']));
        }
        return $travsersed;
    }

    public static function traverseCategoryChildrenByCategoryId($category_id) {
        $travsersed = self::traverseCategoryByCategoryId($category_id);
        array_shift($travsersed);
        return $travsersed;
    }

    public static function getCategoryPathByCategoryId($category_id) {
        $category = self::getFaqCategoryById($category_id);
        $path = array();
        $path[] = $category;
        $parent_id = $category['pid'];
        if ($parent_id != '0')
            $path = array_merge(self::getCategoryPathByCategoryId($parent_id), $path);
        return $path;
    }

    public static function getCategoryPathAsIdsByCategoryId($category_id) {
        $category = self::getFaqCategoryById($category_id);
        $path = array();
        $path[] = $category_id;
        $parent_id = $category['pid'];
        if ($parent_id != '0') {
            $path = array_merge(self::getCategoryPathAsIdsByCategoryId($parent_id), $path);
        }
        return $path;
    }

    public static function tableCreate() {
        $o = new Category;

        if (\Schema::connection($o->connection)->hasTable($o->table) == false) {
            return \Schema::connection($o->connection)->create($o->table, function (\Illuminate\Database\Schema\Blueprint $table) use ($o) {
                        $table->engine = 'InnoDB';
                        $table->string($o->primaryKey, 40)->primary();
                        $table->string('ParentId', 40)->index();
                        $table->integer('Position')->index();
                        $table->string('Status', 50)->default('Published')->index();
                        $table->string('Title', 255)->index();
                        $table->datetime('CreatedAt')->nullable()->default(null);
                        $table->datetime('DeletedAt')->nullable()->default(null);
                        $table->datetime('UpdatedAt')->nullable()->default(null);
                    });
        }

        return true;
    }

    public static function tableDelete() {
        $o = new Category;
        return \Schema::connection($o->connection)->drop($o->table);
    }

}
