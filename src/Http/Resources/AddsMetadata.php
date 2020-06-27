<?php

namespace Core\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
*
*/
trait AddsMetadata
{
    public function getMetadataAttribute()
    {
        return array_keys($this->resource);
    }
}
