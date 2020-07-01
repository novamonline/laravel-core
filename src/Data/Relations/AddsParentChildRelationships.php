<?php

namespace Core\Data\Relations;

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
     * The key used to determine the parent-child relationship
     *
     * @return string
     */
    public function getRelationKey()
    {
        return ( (string)Str::of($this->getTable())->singular() ). '_id';
    }

    public function getRelationKeyAttribute()
    {
        return ( (string)Str::of($this->getTable())->singular() ). '_id';
    }

    /**
     * Scope a query to only include top-most.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopmost($query)
    {
        return $query->whereNull( $this->relationKey );
    }

    /**
     * Fetches the parent of this Model
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne;
     */
    public function Parent()
    {
        //dd($this->primaryKey, $this->getRelationKey());
        return $this->belongsTo(__CLASS__, $this->getRelationKey());
    }

    /**
     * Fetches the immediate children of this class
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function Children()
    {
        return $this->hasMany(__CLASS__);
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
     * All desendants of this __CLASS__ model
     *
     * @return Collection
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
