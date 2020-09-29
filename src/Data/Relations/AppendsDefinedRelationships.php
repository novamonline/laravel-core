<?php

namespace Core\Data\Relations;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 *
 */
trait AppendsDefinedRelationships
{
    public function getRelationshipsAttribute()
    {
        $relationships = new Collection([]);

        $resource = $this->resource ?: $this->toArray();

        foreach ($resource as $field => $value) {
            if (Str::contains($field, '_id')) {
                $name = (string)Str::of($field)->before('_id')->camel()->ucfirst();
                $relationships->$name = $resource->$name ?? null;
            }
        }
        return $relationships;
    }
}
