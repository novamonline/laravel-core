<?php


namespace Core\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CoreController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function response($request, $data, $errors, $code = 200, $message = "")
    {
        $response['success'] = $code >= 200 && $code < 300;
        $response += compact('data', 'errors', 'code', 'message');

        if(!$request->ajax()) {
            return $response;
        }

        return response()->json($response, $code);
    }

    public function setModel($model, $single = false)
    {
        $request = request();

        if($single){
            $model = $model->withTrashed();
        } else {
            $model = $this->getArchived($model, $request);
        }

        $model = $this->getFiltered($model, $request);

        return $model;
    }

    public function getArchived($model, $request)
    {
        $archived = (int) $request->archived;

        switch ($archived){
            case -1:
                return $model->withTrashed();
            case 1:
                return $model->onlyTrashed();
            default:
                return $model;
        }
    }

    public function getFiltered($model, $request)
    {
        $exclude = ['with', 'select', 'page', 'archived', 'limit', 'orphaned'];
        $filter = $request->except($exclude);

        $method = Str::lower($request->method());

        if( $method != 'get' || empty($filter) ) {
            return $model;
        }

        foreach ($filter as $key => $value){
            if(Str::contains($value, $str = ['*', '%'])){

                $value = str_replace($str, "%", $value);
                $model = $model->where($key, 'like', $value);

            } elseif(Str::startsWith($value, ['not:'])){

                $NOT = Str::after($value, 'not:');
                $model = $model->whereNot($key, $NOT);

            } elseif(Str::startsWith($value, ['in:'])){

                $IN = Str::after($value, 'in:');
                $model = $model->whereIn($key, explode(',', $IN));

            } else {

                $model = $model->where($key, $value);

            }
        }

        return $model;
    }

    public function limit()
    {
        return request('limit') ?? 10;
    }

    /**
     * Orphan the specified custom field from storage.
     * @param int $id
     * @return Response
     */
    public function orphan(Request $request, $id)
    {
        $Model = $this->model->withTrashed()->withOrphaned();
        return $this->repo->doOrphan($request, $Model->findOrFail($id));
    }
    /**
     * Restore the orphaned custom field into storage.
     * @param int $id
     * @return Response
     */



}
