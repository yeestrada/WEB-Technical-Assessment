<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class FizzBuzzController extends Controller
{
    // Maximum limits for each functionality
    private const MAX_FIZZBUZZ = 100000;
    private const MAX_FIBONACCI = 10000000;
    private const MAX_COMBINE = 10000000;

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
                    'number' => 'required|integer|min:0|max:' . self::MAX_FIZZBUZZ,
                ]);

                $inputNumber = $validated['number'];
            
                // Limit maximum number to prevent memory/timeout issues
                if ($inputNumber > self::MAX_FIZZBUZZ) {
                    return redirect()->route('fizzbuzz')
                        ->withErrors(['number' => __('fizzbuzz.validation.max_number_exceeded', ['max' => self::MAX_FIZZBUZZ])])
                        ->withInput();
                }
            
                // Get custom rules from the request (max limit for FizzBuzz)
                $customRulesData = $this->extractCustomRules($request, self::MAX_FIZZBUZZ);
                $customRules = $customRulesData['rules'];
                $customRulesArray = $customRulesData['array'];

                try {
                    $result = $this->processFizzBuzz($inputNumber, $customRules);
                } catch (\Exception $e) {
                    return redirect()->route('fizzbuzz')
                        ->withErrors(['number' => __('fizzbuzz.validation.processing_error')])
                        ->withInput();
                }
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
                    'number' => 'required|integer|min:0|max:' . self::MAX_FIBONACCI,
                    'start_x' => 'nullable|integer|min:0|max:' . (self::MAX_FIBONACCI - 1),
                ]);

                $fibInputNumber = $validated['number'];
                $startX = $validated['start_x'] ?? $request->input('start_x', 0);
                // The second value is automatically the next consecutive number
                $startY = $startX + 1;

                // Limit maximum number to prevent memory/timeout issues
                if ($fibInputNumber > self::MAX_FIBONACCI) {
                    return redirect()->route('fibonacci')
                        ->withErrors(['number' => __('fizzbuzz.validation.max_number_exceeded', ['max' => self::MAX_FIBONACCI])])
                        ->withInput();
                }

                try {
                    $fibonacciResult = $this->processFibonacci($fibInputNumber, $startX, $startY);
                } catch (\Exception $e) {
                    return redirect()->route('fibonacci')
                        ->withErrors(['number' => __('fizzbuzz.validation.processing_error')])
                        ->withInput();
                }
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
                    'number' => 'required|integer|min:0|max:' . self::MAX_COMBINE,
                    'start_x' => 'nullable|integer|min:0|max:' . (self::MAX_COMBINE - 1),
                ]);

                $combineInputNumber = $validated['number'];
                $combineStartX = $validated['start_x'] ?? $request->input('start_x', 0);
                // The second value is automatically the next consecutive number
                $combineStartY = $combineStartX + 1;

                // Limit maximum number to prevent memory/timeout issues
                if ($combineInputNumber > self::MAX_COMBINE) {
                    return redirect()->route('combine')
                        ->withErrors(['number' => __('fizzbuzz.validation.max_number_exceeded', ['max' => self::MAX_COMBINE])])
                        ->withInput();
                }

                // Get custom rules from the request (max limit for Combine)
                $customRulesData = $this->extractCustomRules($request, self::MAX_COMBINE);
                $customRules = $customRulesData['rules'];
                $combineCustomRulesArray = $customRulesData['array'];

                try {
                    $combineResult = $this->processCombine($combineInputNumber, $combineStartX, $combineStartY, $customRules);
                } catch (\Exception $e) {
                    return redirect()->route('combine')
                        ->withErrors(['number' => __('fizzbuzz.validation.processing_error')])
                        ->withInput();
                }
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
        
        // Sort custom rules once before the loop for better performance
        ksort($customRules);

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
        // Rules are already sorted (should be sorted before calling this method)
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
        
        // Sort custom rules once before the loop for better performance
        ksort($customRules);
        
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

    /**
     * Extract and validate custom rules from the request
     * Rules that exceed the max limit are simply ignored (not applied)
     * 
     * @param Request $request
     * @param int $maxLimit Maximum allowed value for rule numbers (default: MAX_COMBINE)
     * @return array Returns ['rules' => array, 'array' => array]
     */
    protected function extractCustomRules(Request $request, int $maxLimit = self::MAX_COMBINE): array
    {
        $customRules = [];
        $customRulesArray = [];
        
        if ($request->has('custom_rules')) {
            $rulesInput = $request->input('custom_rules');
            if (is_array($rulesInput)) {
                foreach ($rulesInput as $rule) {
                    if (isset($rule['number']) && isset($rule['word']) 
                        && $rule['number'] !== '' && $rule['word'] !== '') {
                        $ruleNumber = (int)$rule['number'];
                        $ruleWord = trim($rule['word']);
                        
                        // Only add if number is positive, within limits, and word is not empty
                        // Rules exceeding the limit are simply ignored (not applied)
                        if ($ruleNumber > 0 && $ruleNumber <= $maxLimit && $ruleWord !== '') {
                            $customRules[$ruleNumber] = $ruleWord;
                            $customRulesArray[] = [
                                'number' => $ruleNumber,
                                'word' => $ruleWord,
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'rules' => $customRules,
            'array' => $customRulesArray,
        ];
    }
}
