<?php

namespace Core\Data\Models;

// use Core\Data\Models\Model as BaseModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
    * The database table prefix for this model
    *
    * @var string
    */
    protected $prefix;

    /**
     * Overriding parent constructor
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        if (isset($this->prefix)) {
            $this->table = $this->prefix.$this->table;

            /* TODO: We can use the following for migrations */
            $database = Config::get('database');
            $connection = $this->connection ?: 'mysql';
            $database['connections'][$connection]['prefix'] = $this->prefix;
            Config::set('database', $database);
        }

        parent::__construct($attributes);
    }
}
