### Laravel Assessment test

Write a simple API with 1 endpoint. This endpoint accepts a payload as illustrated below

```
{
    "first_name": {
        "value": "John",
        "rules": "alpha|required"
    },
    "last_name": {
        "value": "Doe",
        "rules": "alpha|required"
    },
    "email": {
        "value": "Doe",
        "rules": "email"
    },
    "phone": {
        "value": "08175020329",
        "rules": "number"
    }
}
```

The following requirements exists for the endpoint

1. Implement the endpoint to validate the payload, use the `rules` as the avaliable rules to be validated. IMPLEMENT THE RULES MANULLAY without using standard laravel validation library
2. Your custom validation engine should only support the following rule keys: `alpha, required, email, number`
3. Add support for multiple validation per key, as each validation rule can be separated by pipe character |
4. Response with a `{"status" : true}` if validation passes for the payload
5. Respond with standard validation error payload that laravel would hve responded if this validation fails.


### How to submit
Make a public repo with your Laravel API and share the public link when done


### Test
Using the above payload format, on your postman send a `POST` request to https://nb-test.herokuapp.com/api/validator and the validation rules required above is trigged. Once that is done, WYSIWYG 😉


### Improvements
As the requirements grows, we can consider the following
* Move all validation rules in the controller into a ValidationTrait which can be used in the controller.
* Create a config file which will contains the error messages for each rules.
* On the long run we can optimize the code based on what's needed so we can follow the KISS & YAGNI principle as application.