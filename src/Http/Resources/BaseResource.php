<?php


namespace Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{

    use FiltersResource;

    public function toArray($request)
    {
        $resource = parent::toArray($request);

        if($request->filled('with')){
            foreach(explode(",", $request->with) as $with){
                $resource[$with] = $this->$with;
            }
        }

        return $this->filterRequest($request, $resource);
    }

}
