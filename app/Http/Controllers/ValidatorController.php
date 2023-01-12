<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValidatorController extends Controller
{
    private string $payloadKey;

    private array $supportedRules =  [ 'alpha', 'required', 'email', 'number'];

    private array $errorBag = [];
    
    private array $errorMessages = [
        'alpha' => ':attr must be only letters',
        'number' => ':attr must be a number',
        'email' => ':attr must a valid email address',
        'required' => ':attr is required',
    ];

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

    
    private function validationHandler($rule, $value)
    {
        if (empty($rule)) {
            return ;
        }

        $rules = explode('|', $rule);

        $this->findUnsupportedRules($rules);

        $rulesToEngage  = array_intersect($rules, $this->supportedRules);

        foreach ($rulesToEngage as $key => $rule) {
            $this->{'__'.$rule}($value);
        }
    }

    private function getRules(array $data)
    {
        if (!isset($data['rules']) || empty($data['rules'])) {
            return  null;
        }

        return $data['rules'];
    }
    
    private function getValues(array $data)
    {
        if (!isset($data['value']) || empty($data['value'])) {
            return null;
        }

        return $data['value'];
    }

    private function isRuleSupported(string $rule)
    {
        return in_array($rule, $this->supportedRules);
    }

    private function findUnsupportedRules($rules)
    {
        $unsupportedRules = array_diff($rules, $this->supportedRules);

        if (count($unsupportedRules) > 0) {
            foreach ($unsupportedRules as $v) {
                $this->__error($v);
            }
        }
    }

    private function sanitizeInput($val)
    {
        return filter_var(filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS), FILTER_SANITIZE_ENCODED);
    }

    private function getErrorMessage($rule)
    {
        if (!$rule) {
            return 'The :rules key is not provided';
        }

        if (!$this->isRuleSupported($rule)) {
            return ":$rule rule is not supported";
        }

        return $this->errorMessages[$rule];
    }


    protected function __number($val)
    {
        $val = $this->sanitizeInput($val);

        if (!is_numeric($val)) {
            $this->__error('number');
        }

        return $this;
    }
    
    protected function __alpha($val)
    {
        $val = $this->sanitizeInput($val);

        $isValid = ctype_alpha($val) && (preg_match('/[a-z]/i', $val));

        if (!$isValid) {
            $this->__error('alpha');
        }
        
        return $this;
    }

    protected function __email($val)
    {
        $val = filter_var(filter_var($val, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);

        if (!(preg_match('/[0-9a-z_-]+@[-0-9a-z_^\.]+\.[a-z]{2,3}/i', $val))) {
            $this->__error($this->payloadKey);
        }

        return $this;
    }
    
    protected function __required($val)
    {
        if (empty($val)) {
            $this->__error('required');
        }

        return $this;
    }
    
    protected function __error($attribute, string $message = null)
    {
        $key = $this->payloadKey;

        $message =  $message ?? $this->getErrorMessage($attribute);

        $message = Str::replace(':attr', $key, $message);

        if (Arr::has($this->errorBag, $key)) {
            $this->errorBag[$key] = array_merge((array) $this->errorBag[$key], [$message]);
            
            return $this;
        }

        $this->errorBag[$key] = [$message];
    }
}
