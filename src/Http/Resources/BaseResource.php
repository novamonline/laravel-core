<?php


namespace Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BaseResource extends JsonResource
{

    use FiltersResource;

    public function toArray($request)
    {
        $resource = parent::toArray($request);

        if($request->filled('with')){
            $resource = $this->getWithRelation($request, $resource);
        }

        if($request->boolean('relations')){
            $resource = $this->getAllRelations($request, $resource);
        }

        return $this->filterRequest($request, $resource);
    }

    /**
     * @param $request
     * @param $resource
     * @return mixed
     */
    protected function getWithRelation($request, $resource)
    {
        foreach(explode(",", $request->with) as $with){
            $resource[$with] = $this->$with;
        }
        return $resource;
    }

    /**
     * @param $request
     * @param $resource
     * @return mixed
     */
    protected function getAllRelations($request, $resource)
    {
        foreach($resource as $k => $val){
            if(!Str::endsWith($k, '_id')){
                $resource[$k] = $val;
                continue;
            }
            $with = (string)Str::of($k)->before('_id')->camel()->ucfirst();
            $resource[$with] = $this->$with;
        }
        return $resource;
    }

}
