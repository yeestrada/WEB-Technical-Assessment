<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\FizzBuzzController;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;

class FizzBuzzControllerUnitTest extends TestCase
{
    private FizzBuzzController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new FizzBuzzController();
    }

    /**
     * Test getFizzBuzzOutput with standard rules
     */
    public function test_get_fizz_buzz_output_standard_rules(): void
    {
        $method = $this->getProtectedMethod('getFizzBuzzOutput');
        
        // Test Fizz (multiple of 3)
        $result = $method->invoke($this->controller, 3, []);
        $this->assertEquals('Fizz', $result['output']);
        $this->assertFalse($result['is_custom']);
        $this->assertTrue($result['is_word']);
        
        // Test Buzz (multiple of 5)
        $result = $method->invoke($this->controller, 5, []);
        $this->assertEquals('Buzz', $result['output']);
        $this->assertFalse($result['is_custom']);
        $this->assertTrue($result['is_word']);
        
        // Test FizzBuzz (multiple of both 3 and 5)
        $result = $method->invoke($this->controller, 15, []);
        $this->assertEquals('FizzBuzz', $result['output']);
        $this->assertFalse($result['is_custom']);
        $this->assertTrue($result['is_word']);
        
        // Test regular number
        $result = $method->invoke($this->controller, 7, []);
        $this->assertEquals('7', $result['output']);
        $this->assertFalse($result['is_custom']);
        $this->assertFalse($result['is_word']);
    }

    /**
     * Test getFizzBuzzOutput with custom rules
     */
    public function test_get_fizz_buzz_output_with_custom_rules(): void
    {
        $method = $this->getProtectedMethod('getFizzBuzzOutput');
        $customRules = [7 => 'Bar', 11 => 'Foo'];
        
        // Test custom rule
        $result = $method->invoke($this->controller, 7, $customRules);
        $this->assertEquals('Bar', $result['output']);
        $this->assertTrue($result['is_custom']);
        $this->assertTrue($result['is_word']);
        
        // Test multiple custom rules
        $result = $method->invoke($this->controller, 77, $customRules); // 7 * 11
        $this->assertStringContainsString('Bar', $result['output']);
        $this->assertStringContainsString('Foo', $result['output']);
        $this->assertTrue($result['is_custom']);
        
        // Test combination of standard and custom rules
        $result = $method->invoke($this->controller, 21, $customRules); // 3 * 7
        $this->assertStringContainsString('Fizz', $result['output']);
        $this->assertStringContainsString('Bar', $result['output']);
    }

    /**
     * Test getFizzBuzzOutput with zero
     */
    public function test_get_fizz_buzz_output_zero(): void
    {
        $method = $this->getProtectedMethod('getFizzBuzzOutput');
        
        // Zero is multiple of everything
        $result = $method->invoke($this->controller, 0, []);
        $this->assertEquals('FizzBuzz', $result['output']);
        
        // Zero with custom rules
        $customRules = [7 => 'Bar'];
        $result = $method->invoke($this->controller, 0, $customRules);
        $this->assertStringContainsString('FizzBuzz', $result['output']);
        $this->assertStringContainsString('Bar', $result['output']);
    }

    /**
     * Test processFizzBuzz
     */
    public function test_process_fizz_buzz(): void
    {
        $method = $this->getProtectedMethod('processFizzBuzz');
        $result = $method->invoke($this->controller, 5, []);
        
        $this->assertCount(6, $result); // 0 to 5
        $this->assertEquals(0, $result[0]['number']);
        $this->assertEquals(5, $result[5]['number']);
        $this->assertEquals('FizzBuzz', $result[0]['output']);
        $this->assertEquals('Buzz', $result[5]['output']);
    }

    /**
     * Test processFibonacci with default values
     */
    public function test_process_fibonacci_default(): void
    {
        $method = $this->getProtectedMethod('processFibonacci');
        $result = $method->invoke($this->controller, 20, 0, 1);
        
        $this->assertContains(0, $result);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
        $this->assertContains(5, $result);
        $this->assertContains(8, $result);
        $this->assertContains(13, $result);
        $this->assertNotContains(21, $result); // Exceeds max
    }

    /**
     * Test processFibonacci with custom start values
     */
    public function test_process_fibonacci_custom_start(): void
    {
        $method = $this->getProtectedMethod('processFibonacci');
        $result = $method->invoke($this->controller, 20, 2, 3);
        
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
        $this->assertContains(5, $result); // 2 + 3
        $this->assertContains(8, $result); // 3 + 5
        $this->assertContains(13, $result); // 5 + 8
    }

    /**
     * Test processCombine
     */
    public function test_process_combine(): void
    {
        $method = $this->getProtectedMethod('processCombine');
        $result = $method->invoke($this->controller, 20, 0, 1, []);
        
        $this->assertNotEmpty($result);
        
        // Check that FizzBuzz rules are applied
        $outputs = array_column($result, 'output');
        $this->assertContains('Fizz', $outputs); // 3
        $this->assertContains('Buzz', $outputs); // 5
    }

    /**
     * Test processCombine with custom rules
     */
    public function test_process_combine_with_custom_rules(): void
    {
        $method = $this->getProtectedMethod('processCombine');
        $customRules = [7 => 'Bar'];
        $result = $method->invoke($this->controller, 20, 0, 1, $customRules);
        
        $this->assertNotEmpty($result);
        
        // Check that custom rules are applied
        $outputs = array_column($result, 'output');
        // 7 might be in the sequence depending on start values
    }

    /**
     * Test extractCustomRules
     */
    public function test_extract_custom_rules(): void
    {
        $method = $this->getProtectedMethod('extractCustomRules');
        
        $request = Request::create('/test', 'GET', [
            'custom_rules' => [
                ['number' => '7', 'word' => 'Bar'],
                ['number' => '11', 'word' => 'Foo'],
            ]
        ]);
        
        $result = $method->invoke($this->controller, $request, 100);
        
        $this->assertArrayHasKey('rules', $result);
        $this->assertArrayHasKey('array', $result);
        $this->assertEquals('Bar', $result['rules'][7]);
        $this->assertEquals('Foo', $result['rules'][11]);
        $this->assertCount(2, $result['array']);
    }

    /**
     * Test extractCustomRules filters out rules exceeding limit
     */
    public function test_extract_custom_rules_filters_exceeding_limit(): void
    {
        $method = $this->getProtectedMethod('extractCustomRules');
        
        $request = Request::create('/test', 'GET', [
            'custom_rules' => [
                ['number' => '7', 'word' => 'Bar'],
                ['number' => '150', 'word' => 'Exceeded'], // Exceeds limit of 100
            ]
        ]);
        
        $result = $method->invoke($this->controller, $request, 100);
        
        $this->assertArrayHasKey(7, $result['rules']);
        $this->assertArrayNotHasKey(150, $result['rules']); // Should be filtered out
        $this->assertCount(1, $result['array']);
    }

    /**
     * Test extractCustomRules with empty rules
     */
    public function test_extract_custom_rules_empty(): void
    {
        $method = $this->getProtectedMethod('extractCustomRules');
        
        $request = Request::create('/test', 'GET');
        
        $result = $method->invoke($this->controller, $request, 100);
        
        $this->assertEmpty($result['rules']);
        $this->assertEmpty($result['array']);
        $this->assertEmpty($result['ignored']);
    }

    /**
     * Test extractCustomRules filters out rules for 3 and 5 when filterStandardRules is true
     */
    public function test_extract_custom_rules_filters_standard_rules(): void
    {
        $method = $this->getProtectedMethod('extractCustomRules');
        
        $request = Request::create('/test', 'GET', [
            'custom_rules' => [
                ['number' => '3', 'word' => 'CustomFizz'],
                ['number' => '5', 'word' => 'CustomBuzz'],
                ['number' => '7', 'word' => 'Bar'],
            ]
        ]);
        
        $result = $method->invoke($this->controller, $request, 100, true);
        
        // Rules 3 and 5 should be ignored
        $this->assertArrayNotHasKey(3, $result['rules']);
        $this->assertArrayNotHasKey(5, $result['rules']);
        // Rule 7 should be included
        $this->assertArrayHasKey(7, $result['rules']);
        $this->assertEquals('Bar', $result['rules'][7]);
        // Ignored rules should be tracked
        $this->assertContains(3, $result['ignored']);
        $this->assertContains(5, $result['ignored']);
        $this->assertCount(1, $result['array']); // Only rule 7
    }

    /**
     * Test extractCustomRules does not filter standard rules when filterStandardRules is false
     */
    public function test_extract_custom_rules_does_not_filter_when_flag_false(): void
    {
        $method = $this->getProtectedMethod('extractCustomRules');
        
        $request = Request::create('/test', 'GET', [
            'custom_rules' => [
                ['number' => '3', 'word' => 'CustomFizz'],
                ['number' => '5', 'word' => 'CustomBuzz'],
            ]
        ]);
        
        $result = $method->invoke($this->controller, $request, 100, false);
        
        // Rules 3 and 5 should be included when flag is false
        $this->assertArrayHasKey(3, $result['rules']);
        $this->assertArrayHasKey(5, $result['rules']);
        $this->assertEmpty($result['ignored']);
    }

    /**
     * Helper method to access protected methods
     */
    private function getProtectedMethod(string $methodName): ReflectionMethod
    {
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}

