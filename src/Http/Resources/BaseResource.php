<?php


namespace Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    use FiltersResource, ExtendsResource;

    public function toArray($request)
    {
        $resource = parent::toArray($request);

        if($request->filled('with')){
            $resource = $this->getWithRelation($request, $resource);
        }

        if($request->boolean('relations')){
            $resource = $this->getAllRelations($request, $resource);
        }

        return $this->filteredResult($request, $resource);
    }
}
