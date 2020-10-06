<?php


namespace Core\Boot\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if (!SupportCollection::hasMacro('paginate')) {

            SupportCollection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
                return $this->paginate($perPage, $total, $page, $pageName);
            });
        }

        if (!EloquentCollection::hasMacro('paginate')) {

            EloquentCollection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
                return $this->paginate($perPage, $total, $page, $pageName);
            });
        }
    }

    public function paginate($perPage, $total = null, $page = null, $pageName = 'page')
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $total ?: $this->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }
}
