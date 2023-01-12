<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValidatorController extends Controller
{
    /**
     * Current payload validation  key
     *
     * @var string
     */
    private string $payloadKey;

    /**
     * Supported validation rules
     *
     * @var array
     */
    private array $supportedRules =  [ 'alpha', 'required', 'email', 'number'];

    /**
     * The errors to be returend to the user.
     *
     * @var array
     */
    private array $errorBag = [];
    
    /**
     * Default error messag for specified rules
     *
     * @var array
     */
    private array $errorMessages = [
        'alpha' => ':attr must be only letters',
        'number' => ':attr must be a number',
        'email' => ':attr must a valid email address',
        'required' => ':attr is required',
    ];

    /**
     * Accept a structured payload and validates it.
     *
     * @return response()
     */
    public function __invoke()
    {
        $payload = request()->all();

        if (count($payload) == 0) {
            return response(['message' => 'No data provided'], 422);
        }

        foreach ($payload as $key => $item) {
            $this->payloadKey = $key;

            $rules = $this->getRules($item);

            $value = $this->getValues($item);
            
            if (!$rules && !$value) {
                continue;
            }

            $this->validationHandler($rules, $value);
        }

        if (count($this->errorBag) > 0) {
            return response([ 'message' => 'The given data was invalid.', 'errors' => $this->errorBag], 422);
        }

        return response(['status' => true]);
    }

    
    /**
     * Handles the main
     *
     * @param [any] $rule
     * @param [any] $value
     * @return void
     */
    private function validationHandler($rule, $value)
    {
        if (!$rule) {
            return ;
        }

        $rules = explode('|', $rule);

        $this->findUnsupportedRules($rules);

        $rulesToEngage  = array_intersect($rules, $this->supportedRules);

        foreach ($rulesToEngage as $key => $rule) {
            $this->{'__'.$rule}($value);
        }
    }

    /**
     * Get actual content of the rules key
     *
     * @param array $data
     * @return void
     */
    private function getRules(array $data)
    {
        if (!isset($data['rules']) || !$data['rules']) {
            return  null;
        }

        return $data['rules'];
    }
    
    /**
     * Get actual content of the value key
     *
     * @param array $data
     * @return any
     */
    private function getValues(array $data)
    {
        if (!isset($data['value']) || !$data['value']) {
            return null;
        }

        return $data['value'];
    }

    /**
     * Determine if rule is supported
     *
     * @param string $rule
     * @return boolean
     */
    private function isRuleSupported(string $rule)
    {
        return in_array($rule, $this->supportedRules);
    }

    /**
     * Append unsupported rules to error bag
     *
     * @param array $rules
     * @return string
     */
    private function findUnsupportedRules(array $rules)
    {
        $unsupportedRules = array_diff($rules, $this->supportedRules);

        if (count($unsupportedRules) > 0) {
            foreach ($unsupportedRules as $v) {
                $this->__error($v);
            }
        }
    }

    /**
     * Remove unwanted characters
     *
     * @param [any] $val
     * @return string
     */
    private function sanitizeInput($val)
    {
        return filter_var(filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS), FILTER_SANITIZE_ENCODED);
    }

    /**
     * Returns an error message
     *
     * @param [string] $rule
     * @return string
     */
    private function getErrorMessage(string $rule)
    {
        if (!$rule) {
            return 'The :rules key is not provided';
        }

        if (!$this->isRuleSupported($rule)) {
            return "$rule rule is not supported";
        }

        return $this->errorMessages[$rule];
    }


    /**
     * Number validation rule
     *
     * @param [any] $val
     * @return $this
     */
    protected function __number($val)
    {
        $val = $this->sanitizeInput($val);

        if (!is_numeric($val)) {
            $this->__error('number');
        }

        return $this;
    }
    
    /**
     * Alpha validation rule
     *
     * @param [any] $val
     * @return $this
     */
    protected function __alpha($val)
    {
        $val = $this->sanitizeInput($val);

        $isValid = ctype_alpha($val) && (preg_match('/[a-z]/i', $val));

        if (!$isValid) {
            $this->__error('alpha');
        }
        
        return $this;
    }

    /**
     * Email validation rule
     *
     * @param [type] $val
     * @return $this
     */
    protected function __email($val)
    {
        $val = filter_var(filter_var($val, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);

        if (!(preg_match('/[0-9a-z_-]+@[-0-9a-z_^\.]+\.[a-z]{2,3}/i', $val))) {
            $this->__error($this->payloadKey);
        }

        return $this;
    }
    
    /**
     * Required validation rule
     *
     * @param [type] $val
     * @return void
     */
    protected function __required($val)
    {
        if (empty($val)) {
            $this->__error('required');
        }

        return $this;
    }
    
    /**
     * Add error message into error bag
     *
     * @param [type] $attribute
     * @param string|null $message
     * @return void
     */
    protected function __error($attribute, string $message = null)
    {
        $key = $this->payloadKey;

        $message =  $message ?? $this->getErrorMessage($attribute);

        $message = Str::replace(':attr', $key, $message);

        if (Arr::has($this->errorBag, $key)) {
            $this->errorBag[$key] = array_merge((array) $this->errorBag[$key], [$message]);
        } else {
            $this->errorBag[$key] = [$message];
        }
    }
}
