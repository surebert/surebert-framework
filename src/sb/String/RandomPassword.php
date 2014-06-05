<?php

/**
 * Generates a random lower/upper letter + numbers password of a specified length
 * Makes sure at least one char is cpaitalize
 *
 * Makes sure at least one symbol is used
 * 
 * @deprecated use \sb\Password\Random() instead, this is just kept for 
 * backwards compat purposes
 *
 * @author paul.visco@roswellpark.org
 * @package String
 */
namespace sb\String;

class RandomPassword extends \sb\Password\Random{}