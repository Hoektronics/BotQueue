<?php

namespace App\Providers;

use App;
use App\Validation\Validators\CustomValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{

    protected $rules = [
        'extension' => App\Validation\Validators\ExtensionValidator::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var CustomValidator $rule_class */
        foreach ($this->rules as $rule_name => $rule_class) {
            Validator::extend($rule_name, function ($attribute, $value, $parameters, $validator) {
                /** @var CustomValidator $instance */
                $instance = unserialize($parameters[0]);

                return $instance->passes($attribute, $value);
            });

            Validator::replacer($rule_name, function ($message, $attribute, $rule, $parameters) {
                /** @var CustomValidator $instance */
                $instance = unserialize($parameters[0]);

                return $instance->message($attribute);
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
