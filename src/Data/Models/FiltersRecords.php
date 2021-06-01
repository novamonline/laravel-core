<?php


namespace Core\Data\Models;


use Illuminate\Support\Str;

trait FiltersRecords
{
    public function filtered($filters = [])
    {
        $FILTERS = $filters ?: request()->all();

        $OPTIONS = $this->getAttributes();

        foreach ($FILTERS as $key => $value){
            if(isset($OPTIONS[$key])) {
                $this->checkFilters($key, $value, $this);
            }
        }

        return $this;
    }

    public function checkFilters($key, $value, &$FILTERED)
    {

        if(Str::endsWith('%', $value) || Str::startsWith('%', $value)){
            $FILTERED = $FILTERED->where($key, 'like', str_replace('%', '', $value));
            //
        } elseif(Str::startsWith('!', $value)){
            $FILTERED = $FILTERED->whereNot($key, $value);
            //
        } else {
            $FILTERED = $FILTERED->where($key, $value);
            //
        }

        return $FILTERED;
    }
}
