<?php

namespace App\Providers;

use App\Models\Config;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Schema;
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
        // JsonResource::withoutWrapping();

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


        try { // If can't connect to database this code not affect in runtime

            // Set runtime config from App\Models\Config
            if (Schema::hasTable(app(Config::class)->getTable())) {
                $configs = Config::all();
                $mappedConfigs = [];
                foreach ($configs as $config) {
                    $mappedConfigs[$config->key] = $config->data;
                }
                config($mappedConfigs);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
