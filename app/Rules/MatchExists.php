<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class MatchExists implements Rule
{
    protected $fields;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($fields)
    {
        $this->fields = collect();

        foreach ($fields as $key => $type) {
            $this->fields->push(new MatchFieldSet($key, $type));
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        /** @var MatchFieldSet $field */
        $field = $this->fields->first(function ($field) use ($value) {
            /** @var MatchFieldSet $field */
            return $field->matches($value);
        });

        if($field !== null)
            return $field->exists($value);

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The selected :attribute is invalid.";
    }

    public function getModel($value)
    {
        /** @var MatchFieldSet $field */
        foreach ($this->fields as $field) {
            if ($field->matches($value)) {
                return $field->getModel($value);
            }
        }

        return null;
    }
}

class MatchFieldSet
{
    protected $regex_pattern;
    protected $attributes;
    protected $type;

    public function __construct($key, $type)
    {
        $matches = [];
        preg_match_all('/\{(\w+)\}/', $key, $matches);

        $variables = isset($matches[1]) ? collect($matches[1]) : collect();

        $this->regex_pattern = '/' . preg_replace('/\{(\w+)\}/', '(.*)', $key) . '/';

        $this->attributes = $variables;

        $this->type = $type;
    }

    public function matches($value)
    {
        $matches = $this->getMatches($value);

        return !is_null($matches);
    }

    public function exists($value)
    {
        $model = $this->getModel($value);

        return !is_null($model);
    }

    public function getModel($value)
    {
        $matches = $this->getMatches($value);

        $keyed_match = $this->attributes->combine($matches);

        $builder = $this->getBuilder();

        if ($builder == null) {
            return null;
        }

        foreach ($keyed_match as $key => $match) {
            $builder->where($key, $match);
        }

        return $builder->first();
    }

    protected function getBuilder()
    {
        if (is_subclass_of($this->type, Model::class)) {
            /** @var Model $object */
            $object = app($this->type);
            return $object->newQuery();
        }
        if (is_a($this->type, \Illuminate\Database\Eloquent\Builder::class)) {
            return $this->type;
        }

        return null;
    }

    /**
     * @param $value
     * @return array|null
     */
    protected function getMatches($value)
    {
        $matches = [];
        $patternMatched = preg_match_all($this->regex_pattern, $value, $matches);

        if ($patternMatched == false) {
            return null;
        }

        if (!isset($matches[1])) {
            return null;
        }

        return $matches[1];
    }
}
