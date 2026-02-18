<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| This file contains the configuration for the Pest testing framework.
|
*/

uses(TestCase::class)
    ->in('tests');

/*
|--------------------------------------------------------------------------
| Refresh Database
|--------------------------------------------------------------------------
|
| This trait will reset the database after each test.
|
*/

uses(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('tests/Feature');

uses(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('tests/Unit');

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
|
| Here you may register code that runs before or after each test.
|
*/

beforeEach(function () {
    // Setup before each test
});

afterEach(function () {
    // Teardown after each test
});
