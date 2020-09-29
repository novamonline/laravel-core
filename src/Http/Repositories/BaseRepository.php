<?php

namespace Core\Http\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BaseRepository
{
   use HandlesResponse;

    public function __construct()
    {
        $this->setResult('status', 201);
        $this->setResult('message', null);
    }

    /**
     * @param Request $request
     * @param Model $Model
     * @return object
     */
    public function updateOne(Request $request, Model $Model)
    {
        $input = $request->all();
        foreach($input as $key => $value){
            if(is_array($value)){
                $arrayData = (array)$Model->$key;
                foreach($value as $k => $val){
                    $arrayData[$k] = $val;
                }
                $input[$key] = $arrayData;
            }
        }
        $Model->fill($input)->save();
        return $Model;
    }

    public function createNew($requestData, Model $model)
    {
//        $Model = new $model;

//        if(is_array(Arr::first($requestData))){
//            foreach($requestData as $key => $data){
//                $Model->$k = $model->create($data);
//            }
//        } else {
//            $Model = $model->create($requestData);
//        }
        return $model->create($requestData);
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return mixed
     */
    public function create(Request $request, Model $model)
    {
        try {
            $data = $this->createNew($request->all(), $model);

            $this->setResult([
                'message' => __('Successfully created new record(s)'),
                'data' => $data,
            ]);
            //
        } catch(\Exception $ex){
            $this->setException($ex);
        }
        return $this->getResult();
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return object
     */
    public function update(Request $request, Model $model)
    {
        try {
            $Model = $this->updateOne($request, $model);
            $this->setResult([
                'status' => 201,
                'message' => __('Successfully updated new records!'),
                'data' => Arr::except($Model->toArray(), 'fields'),
            ]);
            //
        } catch(\Exception $ex){
            $this->setException($ex);
        }
        return $this->getResult();
    }

    /**
     * @param Request $request
     * @param Model $model
     */
    public function delete(Request $request, Model $model)
    {
       
        try {
            $IDs = $request->id;
            if(empty($IDs)){
                $model->delete();
            } else {
                $model->delete($IDs);
            }
            $this->setResult([
                'message' =>  __('Successfully deleted record(s)'),
                'data' => $model->toArray(),
            ]);
            //
        } catch(\Exception $ex){
            $this->setException($ex);
        }
        return $this->getResult();
    }
}
