<?php

namespace App\Providers;

use App;
use App\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{

    protected $rules = [
        'extension' => App\Validation\ExtensionRule::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var ValidationRule $rule_class */
        foreach ($this->rules as $rule_name => $rule_class) {
            $instance = $this->app->make($rule_class);

            Validator::extend($rule_name, function ($attribute, $value, $parameters, $validator) use ($instance) {
                return $instance->passes($attribute, $value, $parameters, $validator);
            });

            Validator::replacer($rule_name, function ($message, $attribute, $rule, $parameters) use ($instance) {
                return $instance->replacer($message, $attribute, $rule, $parameters);
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
