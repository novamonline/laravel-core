<?php

namespace Core\Http\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BaseRepository
{

    protected $result;

    public function __construct()
    {
        $this->result = [
            'status' => 200,
            'message' => null
        ];
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
                $arrayData = $Model->$key;
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
        if(is_array(Arr::first($requestData))){
            foreach($requestData as $data){
                $this->result['data'][] = $model->create($data);
            }
        } else {
            $this->result['data'] = $model->create($requestData);
        }
        return $this->result['data'];
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return mixed
     */
    public function create(Request $request, Model $model)
    {
        try {
            $this->result['data'] = $this->createNew($request->all(), $model);
            $this->result['message'] = 'Successfully created new record(s)';
            //
        } catch(\Exception $ex){
            $this->result['code'] = $ex->getCode();
            $this->result['message'] = $ex->getMessage();
            $this->result['trace'] = $ex->getTrace();
        }
        return $this->result;
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return object
     */
    public function update(Request $request, Model $model)
    {
        try {
            $this->result['data'] = $this->updateOne($request, $model);
            $this->result['message'] = 'Successfully updated record(s)';
            //
        } catch(\Exception $ex){
            $this->result['code'] = $ex->getCode();
            $this->result['message'] = $ex->getMessage();
            $this->result['trace'] = $ex->getTrace();
        }
        return $this->result;
    }

    /**
     * @param Request $request
     * @param Model $model
     */
    public function delete(Request $request, Model $model)
    {
        try {
            $this->result['data'] = $model->delete($request->all());
            $this->result['message'] = 'Successfully deleted record(s)';
            //
        } catch(\Exception $ex){
            $this->result['code'] = $ex->getCode();
            $this->result['message'] = $ex->getMessage();
            $this->result['trace'] = $ex->getTrace();
        }
        return $this->result;
    }
}
