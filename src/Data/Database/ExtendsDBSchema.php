<?php

namespace Core\Data\Database;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;

/**
* ExtendsSchema
*/
trait ExtendsDBSchema
{

    /**
    * @var string
    */
    public $user_table = 'bu_users';

    /**
    * @var string
    */
    public $user_id = 'bu_user_id';

    /**
    * @var string
    */
    public $business_table = 'bu_units';

    /**
    * @var string
    */
    public $business_id = 'bu_unit_id';

    /**
    * @var string
    */
    public $created_by = 'created_by_id';

    /**
    * @var string
    */
    public $updated_by = 'updated_by_id';

    /**
    * @var string
    */
    public $deleted_by = 'deleted_by_id';

    /**
    * Simplifies adding foreign key relationships
    * This field cascades on update, but does on delete
    *
    * @param Blueprint $table
    * @param mixed $foreignKey
    * @param null $foreignTable
    *
    * @return ColumnDefinition
    */
    public function foreignId(Blueprint $table, $fieldName, $foreignKey = "id", $foreignTable = null): ForeignKeyDefinition
    {
        Schema::disableForeignKeyConstraints();
        $foreignID = $table->foreignId($fieldName)->constrained($foreignTable, $foreignKey);
        return $foreignID->onUpdateCascade();
    }

    /**
    * Simplifies adding a nullable foreign key relationship
    *
    * @param Blueprint $table
    * @param mixed $foreignKey
    * @param null $foreignTable
    *
    * @return ColumnDefinition
    */
    public function nullableForeignId(Blueprint $table, $fieldName, $foreignKey = "id", $foreignTable = null): ForeignKeyDefinition
    {
        Schema::disableForeignKeyConstraints();
        $foreignID = $table->foreignId($fieldName)->nullable()->constrained($foreignTable, $foreignKey);
        return $foreignID->onUpdateCascade();
    }
    /**
    * Adds our common users who perform the add or update functions, i.e created_by_id and updated__id
    *
    * $fields = -1 means both fields are required;
    * $fields = 0 means only created_by_id is required;
    * $fields = 1 means only updated_by_id is required;
    *
    * $nullable = -1 means both fields are nullable;
    * $nullable = 0 means only created_by_id is nullable;
    * $nullable = 1 means only updated_by_id is nullable;
    *
    * @param Blueprint $table
    * @param integer $precision
    *
    * @return Blueprint $table
    */
    public function userActions(Blueprint $table, $fields = -1, $nullable = -1)
    {

        if ($fields == -1 || $fields = 0) {

            if ($nullable == -1 || $nullable = 0) {
                $this->nullableForeignId($table, $this->created_by, $this->user_table);
            } elseif($nullable == -1 || $nullable = 1) {
                $this->foreignId($table, $this->created_by, $this->user_table);
            }
        }

        if ($fields == -1 || $fields = 1) {

            if ($nullable == -1 || $nullable = 0) {
                $this->nullableForeignId($table, $this->updated_by, $this->user_table);
            } elseif($nullable == -1 || $nullable = 1) {
                $this->foreignId($table, $this->updated_by, $this->user_table);
            }
        }

        return $table;
    }

    /**
    * Adds business identifying foreign keys bu_user_id and bu_unit_id
    *
    * $fields = -1 means both fields are required;
    * $fields = 0 means only bu_user_id is required;
    * $fields = 1 means only bu_unit_id is required;
    *
    * $nullable = -1 means both fields are nullable;
    * $nullable = 0 means only bu_user_id is nullable;
    * $nullable = 1 means only bu_unit_id is nullable;
    *
    * @param Blueprint $table
    * @param undefined $fields
    * @param integer $nullable
    *
    * @return Blueprint
    */
    public function businessIdentity(Blueprint $table, $fields = -1, $nullable = -1): Blueprint
    {
        if ($fields == -1 || $fields = 0) {

            if ($nullable == -1 || $nullable = 0) {
                $this->nullableForeignId($table, $this->user_id, $this->user_table);
            } elseif($nullable == -1 || $nullable = 1) {
                $this->foreignId($table, $this->user_id, $this->user_table);
            }
        }

        if ($fields == -1 || $fields = 1) {

            if ($nullable == -1 || $nullable = 0) {
                $this->nullableForeignId($table, $this->business_id, $this->business_table);
            } elseif($nullable == -1 || $nullable = 1) {
                $this->foreignId($table, $this->business_id, $this->business_table);
            }
        }
        return $table;
    }

    /**
    * Modifies timestamp field
    *
    * @param Blueprint $table
    * @param mixed $fieldName
    *
    * @return ColumnDefinition $dateTime
    */
    public function timestamp(Blueprint $table, $fieldName, $nullable = false): ColumnDefinition
    {
        $dateTime = $table->dateTime($fieldName)->nullable();

        if ($nullable) {
            $NULLABLE_TIMESTAMP = DB::raw('NULL on update CURRENT_TIMESTAMP');
            $dateTime->default($NULLABLE_TIMESTAMP);
        }

        return $dateTime;
    }

    /**
    * Modifies timestamps fields (create and update)
    *
    * @param Blueprint $table
    * @param boolean $softDelete
    * @param boolean $initUpdate
    *
    * @return Blueprint $table
    */
    public function timestamps(Blueprint $table, $softDelete = false, $initUpdate = false) : Blueprint
    {
        $CURRENT_TIMESTAMP  = DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP');
        $NULLABLE_TIMESTAMP = DB::raw('NULL on update CURRENT_TIMESTAMP');
        // $UPDATED_TIMESTAMP  = $initUpdate? $NULLABLE_TIMESTAMP: $CURRENT_TIMESTAMP;

        $table->dateTime('created_at')->default($CURRENT_TIMESTAMP);
        $updateDateTime = $table->dateTime('updated_at')->nullable();

        if ($initUpdate) {
            $updateDateTime->default($NULLABLE_TIMESTAMP);
        }

        if ($softDelete) {
            $table->dateTime('deleted_at')->nullable();
        }

        return $table;
    }

    /**
     * @param Blueprint $table
     * @param string[] $fields
     * @return Blueprint
     */
    public function details(Blueprint $table, $fields = ['name', 'description'])
    {
        $precision = null;

        foreach ($fields  as $key => $field){
            if(is_string($key)){
                $field = $key;
                $precision = $field;
            }
            if(Str::contains($field, 'name')){
                $table->string($field, $precision)->nullable();
                //
            } elseif(Str::contains($field, 'desc')){
                $table->text($field, $precision)->nullable();
            }
        }
        return $table;
    }
}
