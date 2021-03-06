<?php

namespace Core\Data\Models;

use Core\Data\Scopes\OrphaningScope;

/**
 * OrphansData
 */
trait OrphansRecord
{

    /**
     * Indicates if the model is currently force removing.
     *
     * @var bool
     */
    protected $forceRemoving = false;

    /**
     * Boot the record orphaning trait for a model.
     *
     * @return void
     */
    public static function bootOrphansRecord()
    {
        static::addGlobalScope(new OrphaningScope);
    }

    /**
     * Initialize the record orphaning trait for an instance.
     *
     * @return void
     */
    public function initializeOrphansRecord()
    {
        $this->dates[] = $this->getOrphanedAtColumn();
    }

    /**
     * Force a sof delete on an orphaned model.
     *
     * @return bool|null
     */
    public function forceRemove()
    {
        $this->forceRemoving = true;

        return tap($this->orphan(), function ($orphaned) {
            $this->forceRemoving = false;

            if ($orphaned) {
                $this->fireModelEvent('forceRemoved', false);
            }
        });
    }

    /**
     * Perform the actual orphan query on this model instance.
     *
     * @return mixed
     */
    protected function performRemoveOnModel()
    {
        if ($this->forceRemoving) {
            $this->exists = false;

            return $this->setKeysForSaveQuery($this->newModelQuery())->forceRemove();
        }

        return $this->runOrphan();
    }

    /**
     * Perform the actual orphan query on this model instance.
     *
     * @return void
     */
    protected function runOrphan()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getOrphanedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getOrphanedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

    /**
     * Restore an orphaned model instance.
     *
     * @return bool|null
     */
    public function deOrphane()
    {
        // If the deOrphaning event does not return false, we will proceed with this
        // deOrphane operation. Otherwise, we bail out so the developer will stop
        // the deOrphane totally. We will clear the orphaned timestamp and save.
        if ($this->fireModelEvent('deOrphaning') === false) {
            return false;
        }

        $this->{$this->getOrphanedAtColumn()} = null;

        // Once we have saved the model, we will fire the "deOrphaned" event so this
        // developer will do anything they need to after a deOrphane operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('deOrphaned', false);

        return $result;
    }

    /**
     * Determine if the model instance has been orphaned.
     *
     * @return bool
     */
    public function isOrphaned()
    {
        return ! is_null($this->{$this->getOrphanedAtColumn()});
    }

    /**
     * Register a "deOrphaning" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deOrphaning($callback)
    {
        static::registerModelEvent('deOrphaning', $callback);
    }

    /**
     * Register a "deOrphaned" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deOrphaned($callback)
    {
        static::registerModelEvent('deOrphaned', $callback);
    }

    /**
     * Register a "forceRemoved" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function forceRemoved($callback)
    {
        static::registerModelEvent('forceRemoved', $callback);
    }

    /**
     * Determine if the model is currently force removing.
     *
     * @return bool
     */
    public function isForceRemoving()
    {
        return $this->forceRemoving;
    }

    /**
     * Get the name of the "orphaned at" column.
     *
     * @return string
     */
    public function getOrphanedAtColumn()
    {
        return defined('static::ORPHANED_AT') ? static::ORPHANED_AT : 'orphaned_at';
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

}
