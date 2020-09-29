<?php


namespace Core\Http\Repositories;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait AssociatesRelations
{
    protected $result;

    public function associateRelation($association, $message = null)
    {
        try {
            $this->result['data'] = $association;
            $this->result['message'] = $message ?: "Success";
            //
        } catch(\Exception $ex){
            $this->result['code'] = $ex->getCode();
            $this->result['message'] = $ex->getMessage();
            $this->result['trace'] = $ex->getTrace();
        }
        return $this->result;
    }

    public function attach(BelongsToMany $Model, $data)
    {
        return $this->associateRelation(
            $Model->attach($data),
            "Successfully attached new records"
        );
    }

    public function newSync(BelongsToMany $Model, $data)
    {;
        return $this->associateRelation(
            $Model->sync($data),
            "Successfully synced records"
        );
    }

    public function updateSync(BelongsToMany $Model, $data)
    {
        return $this->associateRelation(
            $Model->syncWithoutDetaching($data),
            "Successfully updated synced new records"
        );
    }

    public function detach(BelongsToMany $Model, $data)
    {
        return $this->associateRelation(
            $Model->detach($data),
            "Successfully detached new records"
        );
    }

    public function associate(BelongsTo $Model, $data)
    {
        return $this->associateRelation(
            $Model->associate($data),
            "Successfully associated new records"
        );
    }

    public function createMany(HasMany $Model, $data = [])
    {
        return $this->associateRelation(
            $Model->createMany($data),
            "Successfully created new records"
        );
    }
}
