<?php

namespace App\Providers;

use App;
use App\Validation\CustomValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var ValidationHolder $holder */
        $holder = app(ValidationHolder::class);
        Validator::extend('custom_validator', function ($attribute, $value, $parameters, $validator) use ($holder) {
            /** @var CustomValidator $instance */
            $instance = $holder->get($parameters[0]);

            return $instance->passes($attribute, $value);
        });

        Validator::replacer('custom_validator', function ($message, $attribute, $rule, $parameters) use ($holder) {
            /** @var CustomValidator $instance */
            $instance = $holder->get($parameters[0]);

            return $instance->message($attribute);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ValidationHolder::class, function($app) {
            return new ValidationHolder();
        });
    }
}

class ValidationHolder
{
    protected $rules;

    public function __construct() {
        $this->rules = [];
    }

    public function add($rule, $id)
    {
        $this->rules[$id] = $rule;
    }

    public function get($id)
    {
        return isset($this->rules[$id]) ? $this->rules[$id] : null;
    }
}