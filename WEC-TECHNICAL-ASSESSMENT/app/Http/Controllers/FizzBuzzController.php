<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class FizzBuzzController extends Controller
{
    public function __construct()
    {
        // Set the language based on the lang parameter from URL or session
        if (request()->has('lang')) {
            $locale = request()->get('lang');
            if (in_array($locale, ['en', 'es'])) {
                App::setLocale($locale);
                session(['locale' => $locale]);
            }
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));
        }
    }

    public function fizzbuzz(Request $request)
    {
        $result = null;
        $inputNumber = null;
        $customRules = [];

        if ($request->has('number')) {
            try {
                $validated = $request->validate([
                    'number' => 'required|integer|min:0',
                ]);

                $inputNumber = $validated['number'];
            
                // Get custom rules from the request
                $customRulesArray = [];
                if ($request->has('custom_rules')) {
                    $rulesInput = $request->input('custom_rules');
                    foreach ($rulesInput as $rule) {
                        if (isset($rule['number']) && isset($rule['word']) && $rule['number'] !== '' && $rule['word'] !== '') {
                            $ruleNumber = (int)$rule['number'];
                            $ruleWord = $rule['word'];
                            $customRules[$ruleNumber] = $ruleWord;
                            $customRulesArray[] = [
                                'number' => $ruleNumber,
                                'word' => $ruleWord,
                            ];
                        }
                    }
                }

                $result = $this->processFizzBuzz($inputNumber, $customRules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('fizzbuzz')
                    ->withErrors($e->errors())
                    ->withInput();
            }
        }

        return view('fizzbuzz.index', [
            'result' => $result,
            'inputNumber' => $inputNumber,
            'customRules' => $customRules,
            'customRulesArray' => $customRulesArray ?? [],
            'activeTab' => 'fizzbuzz',
        ]);
    }

    public function fibonacci(Request $request)
    {
        $fibonacciResult = null;
        $fibInputNumber = null;
        $startX = 0;

        if ($request->has('number')) {
            try {
                $validated = $request->validate([
                    'number' => 'required|integer|min:0',
                ]);

                $fibInputNumber = $validated['number'];
                $startX = $request->input('start_x', 0);
                // The second value is automatically the next consecutive number
                $startY = $startX + 1;

                $fibonacciResult = $this->processFibonacci($fibInputNumber, $startX, $startY);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('fibonacci')
                    ->withErrors($e->errors())
                    ->withInput();
            }
        }

        return view('fizzbuzz.index', [
            'fibonacciResult' => $fibonacciResult,
            'fibInputNumber' => $fibInputNumber,
            'startX' => $startX,
            'activeTab' => 'fibonacci',
        ]);
    }

    public function combine(Request $request)
    {
        $combineResult = null;
        $combineInputNumber = null;
        $combineStartX = 0;
        $combineStartY = 1;
        $customRules = [];

        if ($request->has('number')) {
            try {
                $validated = $request->validate([
                    'number' => 'required|integer|min:0',
                ]);

                $combineInputNumber = $validated['number'];
                $combineStartX = $request->input('start_x', 0);
                // The second value is automatically the next consecutive number
                $combineStartY = $combineStartX + 1;

                // Get custom rules from the request
                $combineCustomRulesArray = [];
                if ($request->has('custom_rules')) {
                    $rulesInput = $request->input('custom_rules');
                    foreach ($rulesInput as $rule) {
                        if (isset($rule['number']) && isset($rule['word']) && $rule['number'] !== '' && $rule['word'] !== '') {
                            $ruleNumber = (int)$rule['number'];
                            $ruleWord = $rule['word'];
                            $customRules[$ruleNumber] = $ruleWord;
                            $combineCustomRulesArray[] = [
                                'number' => $ruleNumber,
                                'word' => $ruleWord,
                            ];
                        }
                    }
                }

                $combineResult = $this->processCombine($combineInputNumber, $combineStartX, $combineStartY, $customRules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('combine')
                    ->withErrors($e->errors())
                    ->withInput();
            }
        }

        return view('fizzbuzz.index', [
            'combineResult' => $combineResult,
            'combineInputNumber' => $combineInputNumber,
            'combineStartX' => $combineStartX,
            'customRules' => $customRules,
            'combineCustomRulesArray' => $combineCustomRulesArray ?? [],
            'activeTab' => 'combine',
        ]);
    }

    protected function processFizzBuzz(int $maxNumber, array $customRules = []): array
    {
        $result = [];

        for ($i = 0; $i <= $maxNumber; $i++) {
            $outputData = $this->getFizzBuzzOutput($i, $customRules);
            $result[] = [
                'number' => $i,
                'output' => $outputData['output'],
                'is_custom' => $outputData['is_custom'],
            ];
        }

        return $result;
    }

    protected function getFizzBuzzOutput(int $number, array $customRules): array
    {
        // Build output by concatenating all applicable rules
        $output = '';
        $hasCustomRule = false;

        // Check standard FizzBuzz rules first (3 and 5)
        if ($number % 3 === 0) {
            $output .= 'Fizz';
        }

        if ($number % 5 === 0) {
            $output .= 'Buzz';
        }

        // Check custom rules and concatenate their words
        // Sort rules from smallest to largest for natural ordering
        ksort($customRules);
        
        foreach ($customRules as $ruleNumber => $word) {
            // If the number is a multiple of the custom rule (0 applies to all rules)
            if ($ruleNumber > 0 && $number % $ruleNumber === 0) {
                $output .= $word;
                $hasCustomRule = true;
            }
        }

        // If there's any output (Fizz, Buzz, or custom rules), return it
        if ($output !== '') {
            return [
                'output' => $output,
                'is_custom' => $hasCustomRule,
                'is_word' => true,
            ];
        }

        // If not a multiple of 3, 5, or any custom rule, return the number
        return [
            'output' => (string) $number,
            'is_custom' => false,
            'is_word' => false,
        ];
    }

    protected function processFibonacci(int $maxNumber, int $startX = 0, int $startY = 1): array
    {
        $sequence = [];
        
        if ($maxNumber >= 0) {
            $sequence[] = $startX;
        }
        
        if ($maxNumber >= 1) {
            $sequence[] = $startY;
        }

        $prev = $startX;
        $curr = $startY;

        while ($curr <= $maxNumber) {
            $next = $prev + $curr;
            if ($next > $maxNumber) {
                break;
            }
            $sequence[] = $next;
            $prev = $curr;
            $curr = $next;
        }

        return $sequence;
    }

    protected function processCombine(int $maxNumber, int $startX = 0, int $startY = 1, array $customRules = []): array
    {
        // Get Fibonacci sequence
        $fibonacciSequence = $this->processFibonacci($maxNumber, $startX, $startY);
        
        $result = [];
        
        foreach ($fibonacciSequence as $num) {
            $outputData = $this->getFizzBuzzOutput($num, $customRules);
            $result[] = [
                'output' => $outputData['output'],
                'is_custom' => $outputData['is_custom'],
                'is_word' => $outputData['is_word'],
            ];
        }

        return $result;
    }
}
