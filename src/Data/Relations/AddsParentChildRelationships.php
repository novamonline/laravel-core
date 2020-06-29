<?php

namespace Core\Data\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 * Adds parent-child relationship to the model
 */
trait AddsParentChildRelationships
{

    /**
     * Determines how deep nested relationships should go. Deafult is 10
     *
     * @var integer
     */
    protected $depth = 10;

    /**
     * @return string
     */
    public function getRelationKey()
    {
        return Str::singular($this->getTable()) . '_id';
    }

    /**
     * Scope a query to only include top-most.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopmost($query)
    {
        return $query->whereNull( $this->getRelationKey() );
    }

    /**
     * Fetches the parent of this Model
     *
     * @return void
     */
    public function Parent()
    {
        $FK = Str::singular($this->getTable()).'_id';
        $builder = $this->hasOne(__CLASS__, $FK);
        // dd($builder->toSql(), $builder->getBindings());
        return $builder;
    }

    /**
     * Fetches the ancestors of this Model
     *
     * @return Collection
     */
    public function getAncestorsAttribute()
    {
        $Ancestors = new Collection();

        $Parent = $this->parent;

        $depth = 0;

        while($Parent && $depth < $this->depth){
            $Ancestors->push($Parent);
            $Parent = $Parent->parent;

            $depth++;
        }

        return $Ancestors;
    }

    /**
     * Fetches the immediate children of this class
     *
     * @return void
     */
    public function Children()
    {
        $builder =  $this->hasMany(__CLASS__);
        return $builder;
    }

    /**
     * All desendants of this __CLASS__ model
     *
     * @return HasOneRelationship
     */
    public function getDescendantsAttribute()
    {
        $Descendants = new Collection();

        $depth = 0;
        foreach($this->children ?: [] as $child){

            if (!$child || $depth <= $this->depth) {
                continue;
            }
            $Descendants->push($child);
            $Descendants = $Descendants->merge($child->children);

            $depth++;
        }

        return $Descendants;
    }
}
