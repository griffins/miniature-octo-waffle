# Built with Lumen PHP Framework
[![Build Status](https://github.com/griffins/miniature-octo-waffle/actions/workflows/php.yml/badge.svg)](https://github.com/griffins/miniature-octo-waffle/actions/workflows/php.yml) ![Code Coverage Badge](./badge.svg)

## THE CHALLENGE
Intro: We want to give out promo codes worth x amount during events so people can get
free rides to and from the event. The flaw with that is people can use the promo codes without
going to the event.

Task: Implement a Promo code API with the following features:

- [x] Generation of new promo codes for an event
- [x] The promo code is worth a specific amount of ride 
- [x] The promo code can expire 
- [x] Can be deactivated
- [x] Return active promo codes
- [x] Return all promo codes 
- [x] Only valid when user’s pickup or destination is within x radius of the event venue
- [x] The promo code radius should be configurable
- [x] To test the validity of the promo code, expose an endpoint that accepts origin, destination, the promo code.
- [x] The API should return the promo code details and a polyline using the destination and origin if the promo code is valid and an error otherwise.

## Assumptions in this solution.
- No currency or related items is being implemented
- Here Maps API key is bundled for ease of testing since we use the here maps api to get poly-lines for a route.


## Requirements
- PHP 7.4^

## Installation
- Clone the repo
- Run ```composer install``` to the dependencies
- Copy ```.env.example``` to ```.env``` and add configurations in the .env, at-least database info. 
- Run migrations with ```php artisan migrate```
- Run ```php -S localhost:5000 -t public```

## Run tests
- ```XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text```

## Routes


- ```GET /coupon```   - Lists all coupons
- ```GET /coupon/active``` - Lists active coupons
- ```POST /coupon/de-activate/:id``` - Deactivate a coupon
- ```POST /coupon``` - Adds a new coupon 
- ```PATCH /coupon/:id``` - Updates coupon details
- ```DELETE /coupon/:id``` - Deletes a coupon
- ```DELETE /coupon/apply``` - Apply a coupon 


## COUPON SCHEMA
- code [required]
- description [required]
- amount [required]
- status ['active', 'in-active'] defaults to active
- lat, lng, radius [all required when any of them is non null]
- validFrom, validTo [nullable]

## Extras
- The API has no security for brevity of this challenge
- Set validTo to a past date to expire a coupon
- Set validFrom to make a coupon go live on a certain date
- Radius uses meters as its unit
- Set coupon status to in-active to de-activate it  
- To validate a coupon you need code, destination and origin
