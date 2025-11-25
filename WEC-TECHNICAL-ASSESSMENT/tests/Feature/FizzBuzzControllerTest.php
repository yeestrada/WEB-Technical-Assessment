<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FizzBuzzControllerTest extends TestCase
{
    /**
     * Test FizzBuzz route returns successful response
     */
    public function test_fizzbuzz_route_returns_successful_response(): void
    {
        $response = $this->get('/fizzbuzz');

        $response->assertStatus(200);
        $response->assertViewIs('fizzbuzz.index');
    }

    /**
     * Test FizzBuzz with number parameter
     */
    public function test_fizzbuzz_with_number(): void
    {
        $response = $this->get('/fizzbuzz?number=15');

        $response->assertStatus(200);
        $response->assertViewHas('result');
        $response->assertViewHas('inputNumber', 15);
    }

    /**
     * Test FizzBuzz standard rules (3, 5, 15)
     */
    public function test_fizzbuzz_standard_rules(): void
    {
        $response = $this->get('/fizzbuzz?number=15');
        $result = $response->viewData('result');

        $this->assertNotNull($result);
        $this->assertCount(16, $result); // 0 to 15
        
        // Check Fizz (multiple of 3)
        $this->assertEquals('Fizz', $result[3]['output']);
        $this->assertEquals('Fizz', $result[6]['output']);
        $this->assertEquals('Fizz', $result[9]['output']);
        $this->assertEquals('Fizz', $result[12]['output']);
        
        // Check Buzz (multiple of 5)
        $this->assertEquals('Buzz', $result[5]['output']);
        $this->assertEquals('Buzz', $result[10]['output']);
        
        // Check FizzBuzz (multiple of both 3 and 5)
        $this->assertEquals('FizzBuzz', $result[0]['output']);
        $this->assertEquals('FizzBuzz', $result[15]['output']);
        
        // Check regular numbers
        $this->assertEquals('1', $result[1]['output']);
        $this->assertEquals('2', $result[2]['output']);
        $this->assertEquals('4', $result[4]['output']);
    }

    /**
     * Test FizzBuzz with custom rules
     */
    public function test_fizzbuzz_with_custom_rules(): void
    {
        $response = $this->get('/fizzbuzz?number=21&custom_rules[0][number]=7&custom_rules[0][word]=Bar');
        $result = $response->viewData('result');

        $this->assertNotNull($result);
        
        // Check custom rule (multiple of 7)
        $this->assertEquals('Bar', $result[7]['output']);
        
        // Check combination (multiple of 3 and 7)
        $this->assertStringContainsString('Fizz', $result[21]['output']);
        $this->assertStringContainsString('Bar', $result[21]['output']);
    }

    /**
     * Test FizzBuzz validation - negative number
     */
    public function test_fizzbuzz_validation_negative_number(): void
    {
        $response = $this->get('/fizzbuzz?number=-1');

        $response->assertStatus(302); // Redirect with validation error
        $response->assertSessionHasErrors('number');
    }

    /**
     * Test FizzBuzz validation - number exceeds maximum
     */
    public function test_fizzbuzz_validation_exceeds_maximum(): void
    {
        $response = $this->get('/fizzbuzz?number=100001');

        $response->assertStatus(302); // Redirect with validation error
        $response->assertSessionHasErrors('number');
    }

    /**
     * Test Fibonacci route returns successful response
     */
    public function test_fibonacci_route_returns_successful_response(): void
    {
        $response = $this->get('/fibonacci');

        $response->assertStatus(200);
        $response->assertViewIs('fizzbuzz.index');
    }

    /**
     * Test Fibonacci with default values (0, 1)
     */
    public function test_fibonacci_default_values(): void
    {
        $response = $this->get('/fibonacci?number=20');
        $result = $response->viewData('fibonacciResult');

        $this->assertNotNull($result);
        $this->assertContains(0, $result);
        $this->assertContains(1, $result);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
        $this->assertContains(5, $result);
        $this->assertContains(8, $result);
        $this->assertContains(13, $result);
    }

    /**
     * Test Fibonacci with custom start value
     */
    public function test_fibonacci_with_custom_start(): void
    {
        $response = $this->get('/fibonacci?number=20&start_x=2');
        $result = $response->viewData('fibonacciResult');

        $this->assertNotNull($result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result); // 2 + 1
        $this->assertContains(5, $result); // 2 + 3
        $this->assertContains(8, $result); // 3 + 5
    }

    /**
     * Test Fibonacci validation - negative number
     */
    public function test_fibonacci_validation_negative_number(): void
    {
        $response = $this->get('/fibonacci?number=-1');

        $response->assertStatus(302);
        $response->assertSessionHasErrors('number');
    }

    /**
     * Test Fibonacci validation - start_x exceeds maximum
     */
    public function test_fibonacci_validation_start_x_exceeds_maximum(): void
    {
        $response = $this->get('/fibonacci?number=10&start_x=10000000');

        $response->assertStatus(302);
        $response->assertSessionHasErrors('start_x');
    }

    /**
     * Test Combine route returns successful response
     */
    public function test_combine_route_returns_successful_response(): void
    {
        $response = $this->get('/combine');

        $response->assertStatus(200);
        $response->assertViewIs('fizzbuzz.index');
    }

    /**
     * Test Combine with default values
     */
    public function test_combine_default_values(): void
    {
        $response = $this->get('/combine?number=20');
        $result = $response->viewData('combineResult');

        $this->assertNotNull($result);
        
        // Fibonacci sequence: 0, 1, 1, 2, 3, 5, 8, 13
        // Check that FizzBuzz rules are applied
        $outputs = array_column($result, 'output');
        $this->assertContains('FizzBuzz', $outputs); // 0 should be FizzBuzz
        $this->assertContains('1', $outputs);
        $this->assertContains('2', $outputs);
        $this->assertContains('Fizz', $outputs); // 3
        $this->assertContains('Buzz', $outputs); // 5
    }

    /**
     * Test Combine with custom rules
     */
    public function test_combine_with_custom_rules(): void
    {
        $response = $this->get('/combine?number=20&custom_rules[0][number]=2&custom_rules[0][word]=Bar');
        $result = $response->viewData('combineResult');

        $this->assertNotNull($result);
        
        // Check that custom rules are applied to Fibonacci sequence
        $outputs = array_column($result, 'output');
        // 2 is in Fibonacci sequence (0, 1, 1, 2, 3, 5, 8, 13), should show Bar
        $this->assertContains('Bar', $outputs);
    }

    /**
     * Test Combine validation - number exceeds maximum
     */
    public function test_combine_validation_exceeds_maximum(): void
    {
        $response = $this->get('/combine?number=10000001');

        $response->assertStatus(302);
        $response->assertSessionHasErrors('number');
    }

    /**
     * Test root route redirects to fizzbuzz
     */
    public function test_root_route_redirects_to_fizzbuzz(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('fizzbuzz.index');
    }

    /**
     * Test language parameter sets locale
     */
    public function test_language_parameter_sets_locale(): void
    {
        $response = $this->get('/fizzbuzz?lang=es');

        $response->assertStatus(200);
        $this->assertEquals('es', app()->getLocale());
    }
}

