<?php


namespace Core\Http\Repositories;


use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use PDOException;

trait HandlesResponse
{
   protected $result;
   
   public function recordType($model)
   {
//      return Str::lower($model->getTable());
   }
   
   public function setResult($key, $value = null)
   {
      if(is_array($key)){
         foreach ($key as $k => $val){
            $this->setResult($k, $val);
         }
      } elseif($value) {
         $this->result[$key] = $value;
      } else{
         return;
      }
   }

    public function setEmptyResult($request)
    {
        $this->setResult([
            'status' => 400,
            'message' => __('Empty request sent to the server!'),
            'data' => $request->all(),
        ]);
        return $this;
    }


   
   public function setException(\Exception $exception)
   {
      $status = $exception->getCode();
      $message = $exception->getMessage();
      $trace =  $exception->getTrace();

      if($exception instanceof QueryException || $exception instanceof PDOException){
          http_response_code($status = 500);
          $message = (string) Str::of($message)->after(':')->before('(')->trim();
      }
      
      if(config('app.env') == 'production'){
         $trace = [];
      }
      
      $this->setResult(compact('status', 'message', 'trace'));
      return $this;
   }
   
   public function getResult($key =  null)
   {
      $response = $this->result[$key] ?? $this->result;
      $status = ((int) $this->result['status']) ?: 500;
      if(!$status || $status > 599 || is_string($status)){
         $status = 500;
      }
      return response()->json($response, $status);
   }
}
