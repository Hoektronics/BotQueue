<?php


namespace App\Validation;


use Illuminate\Database\Eloquent\Model;

class MatchExists implements CustomValidator
{
    use CustomValidatorTrait;

    /**
     * @var array
     */
    protected $fields;

    public function __construct($fields) {
        $this->fields = [];

        foreach ($fields as $key => $type) {
            array_push($this->fields, new MatchFieldSet($key, $type));
        }
    }

    public function passes($attribute, $value)
    {
        /** @var MatchFieldSet $field */
        foreach ($this->fields as $field) {
            if ($field->matches($value)) {
                return $field->exists($value);
            }
        }

        return false;
    }

    public function message($attribute)
    {
        return "The selected ${attribute} is invalid.";
    }
}

class MatchFieldSet {
    protected $regex_pattern;
    protected $attributes;
    protected $type;

    public function __construct($key, $type) {
        $matches = [];
        preg_match_all('/\{(\w+)\}/', $key, $matches);

        $variables = isset($matches[1]) ? $matches[1] : [];

        $this->regex_pattern = '/'.preg_replace('/\{(\w+)\}/', '(.*)', $key).'/';

        $this->attributes = $variables;

        $this->type = $type;
    }

    public function matches($value) {
        $matches = $this->getMatches($value);

        return ! is_null($matches);
    }

    public function exists($value)
    {
        $matches = $this->getMatches($value);

        $keyed_match = array_combine($this->attributes, $matches);

        if (is_subclass_of($this->type, Model::class)) {
            /** @var Model $object */
            $object = app($this->type);
            $builder = $object->newQuery();
        } elseif (is_a($this->type, \Illuminate\Database\Eloquent\Builder::class)) {
            $builder = $this->type;
        } else {
            return false;
        }

        foreach ($keyed_match as $key => $match) {
            $builder->where($key, $match);
        }

        return $builder->count() > 0;
    }

    /**
     * @param $value
     * @return array|null
     */
    protected function getMatches($value)
    {
        $matches = [];
        $patternMatched = preg_match_all($this->regex_pattern, $value, $matches);

        if ($patternMatched == false)
            return null;

        if (! isset($matches[1]))
            return null;

        return $matches[1];
    }
}