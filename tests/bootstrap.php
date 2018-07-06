<?php
use PHPUnit\Framework\Assert;

$loader = require __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpIncludeInspection */
require_once dirname((new ReflectionClass(Assert::class))->getFileName()) . '/Assert/Functions.php';
