<?php

namespace Core\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
*
*/
trait FiltersResource
{
    public function filterRequest($request, $resource)
    {
        // handle select parameter
        $resource = $this->getSelected($request, $resource);
        return $resource;
    }
    public function getSelected($request, $resource)
    {
        if ($request->filled('select')) {
            $selected = preg_split("#\s*,\s*#msi", $request->select);
            return Arr::only($resource, $selected);
        }
        return $resource;
    }
}
