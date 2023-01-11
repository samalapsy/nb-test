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
