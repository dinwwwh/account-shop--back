<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Make pagination with Illuminate\Support\Collection
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
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
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Rule for check keys of array
        Validator::extend('keys', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) return false;
            $keys = array_keys($value);
            $validation = Validator::make([
                'keys' => $keys,
            ], [
                'keys.*' => $parameters
            ]);
            return !$validation->fails();
        });
        Validator::replacer('keys', function ($message, $attribute, $rule, $parameters) {
            return 'Keys of this object must pass rules[' . implode(', ', $parameters) . '].';
        });
    }
}
