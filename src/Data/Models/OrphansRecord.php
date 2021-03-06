<?php

namespace Core\Data\Models;

use Core\Data\Scopes\OrphaningScope;

/**
 * OrphansData
 */
trait OrphansRecord
{

    /**
     * Indicates if the model is currently force deleting.
     *
     * @var bool
     */
    protected $forceDeleting = false;

    /**
     * Boot the orphaning trait for a model.
     *
     * @return void
     */
    public static function bootOrphansRecord()
    {
        static::addGlobalScope(new OrphaningScope);
    }
    /*
     * --------
     *  EVENTS
     * --------
     */
    /**
     * Register a orphaning model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function orphaning($callback)
    {
        static::registerModelEvent('orphaning', $callback);
    }

    /**
     * Register a orphaned model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function orphaned($callback)
    {
        static::registerModelEvent('orphaned', $callback);
    }

    /**
     * Register a de-orphaning model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deOrphaning($callback)
    {
        static::registerModelEvent('deOrphaning', $callback);
    }

    /**
     * Register a de-orphaned model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deOrphaned($callback)
    {
        static::registerModelEvent('deOrphaned', $callback);
    }
    /*
     * --------
     *  GETTERS
     * --------
     */
    public function getOrphanedAtColumn()
    {
        return defined('static::ORPHANED_AT') ? static::ORPHANED_AT : 'orphaned_at';
    }

    public function getOrphanedAttribute()
    {
        return ! is_null($this->{$this->getOrphanedAtColumn()});
    }

    /**
     * Get the fully qualified "orphaned at" column.
     *
     * @return string
     */
    public function getQualifiedOrphanedAtColumn()
    {
        return $this->qualifyColumn($this->getOrphanedAtColumn());
    }

    /*
     * --------
     *  ACTIONS
     * --------
     */
    public function runOrphan($time = null)
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $this->{$this->getOrphanedAtColumn()} = $time;

        $columns = [$this->getOrphanedAtColumn() => $this->fromDateTime($time)];

        if ($this->timestamps && ! is_null($this->getOrphanedAtColumn())) {
            $this->{$this->getOrphanedAtColumn()} = $time;

            $columns[$this->getOrphanedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

    protected function performOrphanOnModel()
    {
        if ($this->orphaning) {
            $this->exists = false;

            return $this->setKeysForSaveQuery($this->newModelQuery())->orphan();
        }

        return $this->runOrphan();
    }


    public function orphan()
    {
        $this->orphaning = true;

        return tap($this->orphanData(), function ($orphaning) {
            $this->orphaning = false;

            if ($orphaning) {
                $this->fireModelEvent('orphaning', false);
            }
        });
    }

    public function orphanData()
    {
        $time = $this->freshTimestamp();
        return $this->runOrphanData($time);
    }

    public function deOrphan()
    {
        $this->deOrphaning = true;

        return tap($this->deOrphanData(), function ($deOrphaning) {
            $this->deOrphaning = false;

            if ($deOrphaning) {
                $this->fireModelEvent('deOrphaning', false);
            }
        });
    }

    public function deOrphanData()
    {
        return $this->runOrphanData();
    }

}
