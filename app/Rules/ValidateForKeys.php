<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Validator;

class ValidateForKeys implements Rule
{
    private $rules;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $keys = array_keys($value);
        $validation = Validator::make([
            'keys' => $keys,
        ], [
            'keys.*' => $this->rules
        ]);
        return !$validation->fails();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The keys of :attribute is invalid.';
    }
}
