<?php


namespace Core\Http\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    use FiltersResource, ExtendsResource;


//    public function toArray($request)
//    {
//        $resource = parent::toArray($request);
//
//        if($request->filled('with')){
//            $resource = $this->getWithRelation($request, $resource);
//        }
//
//        if($request->boolean('relations')){
//            $resource = $this->getAllRelations($request, $resource);
//        }
//
//        return $this->filteredResult($request, $resource);
//    }
}
