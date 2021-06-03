# Built with Lumen PHP Framework
[![Build Status](https://github.com/griffins/miniature-octo-waffle/actions/workflows/php.yml/badge.svg)](https://github.com/griffins/miniature-octo-waffle/actions/workflows/php.yml)
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
- [ ] The API should return the promo code details and a polyline using the destination and origin if the promo code is valid and an error otherwise.

## Assumptions in this solution.
- No currency or related items is being implemented
