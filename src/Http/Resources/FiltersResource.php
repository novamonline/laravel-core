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
        if ($request->filled('select')) {
            $resource = $this->getSelected($request->select, $resource);
        }
        if ($filtered = $request->except('select', 'with', 'relations')) {
            $resource = $this->getFiltered($filtered, $resource);
        }
        return $resource;
    }

    public function getSelected($selectData, $resource)
    {
        $selected = preg_split("#\s*,\s*#msi", $selectData);
        return Arr::only($resource, $selected);
    }

    public function getFiltered($filterOptions, $resource)
    {
        return $resource;
    }
}
