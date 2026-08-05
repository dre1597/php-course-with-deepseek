<?php

class Animal {}
class Dog extends Animal {}
interface CanFly {}

$dog = new Dog();

var_dump($dog instanceof Dog);     // true
var_dump($dog instanceof Animal);  // true — subclass
var_dump($dog instanceof CanFly);  // false — doesn't implement it

// PHP 8.0+ accepts class name as string
$class = 'Dog';
var_dump($dog instanceof $class); // true

$value = 42;
var_dump($value instanceof \DateTime); // false
$null = null;
var_dump($null instanceof \DateTime);  // false
