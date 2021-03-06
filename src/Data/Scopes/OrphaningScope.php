<?php


namespace Core\Data\Scopes;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class OrphaningScope implements Scope
{

    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['DeOrphan', 'WithOrphaned', 'WithoutOrphaned', 'OnlyOrphaned'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull($model->getQualifiedOrphanedAtColumn());
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getOrphanedAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Get the "deleted at" column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getOrphanedAtColumn(Builder $builder)
    {
        if (count((array) $builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedOrphanedAtColumn();
        }

        return $builder->getModel()->getOrphanedAtColumn();
    }

    /**
     * Add the deOrphan extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addDeOrphan(Builder $builder)
    {
        $builder->macro('deOrphan', function (Builder $builder) {
            $builder->withOrphaned();

            return $builder->update([$builder->getModel()->getOrphanedAtColumn() => null]);
        });
    }

    /**
     * Add the with-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithOrphaned(Builder $builder)
    {
        $builder->macro('withOrphaned', function (Builder $builder, $withOrphaned = true) {
            if (! $withOrphaned) {
                return $builder->withoutOrphaned();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutOrphaned(Builder $builder)
    {
        $builder->macro('withoutOrphaned', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedOrphanedAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyOrphaned(Builder $builder)
    {
        $builder->macro('onlyOrphaned', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNotNull(
                $model->getQualifiedOrphanedAtColumn()
            );

            return $builder;
        });
    }
}
