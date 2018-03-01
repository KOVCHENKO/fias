<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /** @test */
    function hello_world_test()
    {
        $greeting = 'Hello world';

        $this->assertTrue($greeting == 'Hello world');
    }

    /** @test */
    function test_something_have_something()
    {
        $digits = [1, 2, 3, 4, 5];
        $this->assertContains(5, $digits, 'THIS IS WRONG!');
    }

    /** @test */
    function test_array_has_key()
    {
        /* Prepare */
        $digits = [
            1 => 'first',
            2 => 'second',
            3 => 'third'
        ];

        /* Assert */
        $this->assertArrayHasKey(3, $digits, 'THERE IS NO SUCH KEY IN ARRAY!');
    }


}
