<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('fizzbuzz.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/fizzbuzz.css') }}">
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="language-selector">
                <label for="language">{{ __('fizzbuzz.language_label') }}:</label>
                <select id="language" name="language" onchange="changeLanguage(this.value)">
                    <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                    <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                </select>
            </div>
            <h1>{{ __('fizzbuzz.title') }}</h1>
        </div>
        
        <div class="info-card" id="infoCard">
            <h3>{{ __('fizzbuzz.info_card.overview') }}</h3>
            <div class="info-content">
                <p>{{ __('fizzbuzz.info_card.overview_text') }}</p>
                
                <h4>{{ __('fizzbuzz.info_card.part1_title') }}</h4>
                <p><strong>{{ __('fizzbuzz.info_card.requirements') }}</strong></p>
                <ul>
                    <li>{{ __('fizzbuzz.info_card.part1_requirement1') }}
                        <ul>
                            <li>{{ __('fizzbuzz.info_card.part1_requirement1_item1') }}</li>
                            <li>{{ __('fizzbuzz.info_card.part1_requirement1_item2') }}</li>
                            <li>{{ __('fizzbuzz.info_card.part1_requirement1_item3') }}</li>
                        </ul>
                    </li>
                    <li>{{ __('fizzbuzz.info_card.part1_requirement2') }}</li>
                </ul>

                <h4>{{ __('fizzbuzz.info_card.part2_title') }}</h4>
                <p><strong>{{ __('fizzbuzz.info_card.requirements') }}</strong></p>
                <ul>
                    <li>{{ __('fizzbuzz.info_card.part2_requirement1') }}</li>
                    <li>{{ __('fizzbuzz.info_card.part2_requirement2') }}</li>
                </ul>
                <p>{{ __('fizzbuzz.info_card.part2_note') }}</p>

                <h4>{{ __('fizzbuzz.info_card.part3_title') }}</h4>
                <p><strong>{{ __('fizzbuzz.info_card.requirements') }}</strong></p>
                <ul>
                    <li>{{ __('fizzbuzz.info_card.part3_requirement1') }}</li>
                    <li>{{ __('fizzbuzz.info_card.part3_requirement2') }}</li>
                </ul>
            </div>
            <button class="toggle-info" onclick="toggleInfoCard()">{{ __('fizzbuzz.info_card.hide_instructions') }}</button>
        </div>
        
        <div class="tabs">
            <button class="tab {{ ($activeTab ?? 'fizzbuzz') === 'fizzbuzz' ? 'active' : '' }}" onclick="showTab('fizzbuzz', this)">{{ __('fizzbuzz.tabs.fizzbuzz') }}</button>
            <button class="tab {{ ($activeTab ?? 'fizzbuzz') === 'fibonacci' ? 'active' : '' }}" onclick="showTab('fibonacci', this)">{{ __('fizzbuzz.tabs.fibonacci') }}</button>
            <button class="tab {{ ($activeTab ?? 'fizzbuzz') === 'combine' ? 'active' : '' }}" onclick="showTab('combine', this)">{{ __('fizzbuzz.tabs.combine') }}</button>
        </div>

        <!-- Tab FizzBuzz -->
        <div id="fizzbuzz" class="tab-content {{ ($activeTab ?? 'fizzbuzz') === 'fizzbuzz' ? 'active' : '' }}">
            <div class="form-section">
                <h2>{{ __('fizzbuzz.fizzbuzz.title') }}</h2>
                @if($errors->has('number'))
                    <div class="error-message">{{ $errors->first('number') }}</div>
                @endif
                <form method="GET" action="{{ route('fizzbuzz') }}" id="fizzbuzzForm" onsubmit="return validateForm(this)">
                    @if(request()->has('lang'))
                        <input type="hidden" name="lang" value="{{ request()->get('lang') }}">
                    @endif
                    <div class="form-group">
                        <label for="number">{{ __('fizzbuzz.fizzbuzz.number_label') }}</label>
                        <input type="number" id="number" name="number" min="0" step="1"
                               value="{{ $inputNumber ?? '' }}" required
                               class="{{ $errors->has('number') ? 'input-error' : '' }}">
                        @if($errors->has('number'))
                            <div class="error-message">{{ $errors->first('number') }}</div>
                        @endif
                    </div>
                    
                    <div class="custom-rules">
                        <label>{{ __('fizzbuzz.fizzbuzz.custom_rules') }}</label>
                        <div id="rulesContainer">
                            @if(isset($customRulesArray) && count($customRulesArray) > 0)
                                @foreach($customRulesArray as $index => $rule)
                                <div class="rule-input">
                                    <input type="number" name="custom_rules[{{ $index }}][number]" placeholder="{{ __('fizzbuzz.fizzbuzz.number_placeholder') }}" min="1" value="{{ $rule['number'] }}">
                                    <input type="text" name="custom_rules[{{ $index }}][word]" placeholder="{{ __('fizzbuzz.fizzbuzz.word_placeholder') }}" value="{{ $rule['word'] }}">
                                    <button type="button" onclick="this.parentElement.remove()">×</button>
                                </div>
                                @endforeach
                            @else
                                <div class="rule-input">
                                    <input type="number" name="custom_rules[0][number]" placeholder="{{ __('fizzbuzz.fizzbuzz.number_placeholder') }}" min="1">
                                    <input type="text" name="custom_rules[0][word]" placeholder="{{ __('fizzbuzz.fizzbuzz.word_placeholder') }}">
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addRule()" style="margin-top: 0.5rem;">{{ __('fizzbuzz.fizzbuzz.add_rule') }}</button>
                    </div>

                    <div class="form-actions">
                        <button type="submit">{{ __('fizzbuzz.fizzbuzz.process') }}</button>
                    </div>
                </form>
            </div>

            @if(isset($result))
            <div class="form-section">
                <h2>{{ __('fizzbuzz.fizzbuzz.result_title', ['number' => $inputNumber]) }}</h2>
                <div class="result-grid" id="fizzbuzzResults">
                    @foreach($result as $item)
                    <div class="result-item {{ strtolower($item['output']) }} {{ $item['is_custom'] ?? false ? 'custom-rule' : '' }}" title="{{ htmlspecialchars($item['output'], ENT_QUOTES, 'UTF-8') }}">
                        {{ strlen($item['output']) > 8 ? substr($item['output'], 0, 8) . '...' : $item['output'] }}
                    </div>
                    @endforeach
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-download" onclick="downloadFizzBuzzResults()">{{ __('fizzbuzz.fizzbuzz.download') }}</button>
                </div>
            </div>
            @endif
        </div>

        <!-- Tab Fibonacci -->
        <div id="fibonacci" class="tab-content {{ ($activeTab ?? 'fizzbuzz') === 'fibonacci' ? 'active' : '' }}">
            <div class="form-section">
                <h2>{{ __('fizzbuzz.fibonacci.title') }}</h2>
                @if($errors->has('number'))
                    <div class="error-message">{{ $errors->first('number') }}</div>
                @endif
                <form method="GET" action="{{ route('fibonacci') }}" id="fibonacciForm" onsubmit="return validateForm(this)">
                    @if(request()->has('lang'))
                        <input type="hidden" name="lang" value="{{ request()->get('lang') }}">
                    @endif
                    <div class="form-group">
                        <label>{{ __('fizzbuzz.fibonacci.initial_value') }}</label>
                        <input type="number" name="start_x" placeholder="{{ __('fizzbuzz.fibonacci.start_x') }}" value="{{ isset($startX) ? $startX : 0 }}">
                        <small style="color: #666; font-size: 0.875rem; display: block; margin-top: 0.25rem;">{{ __('fizzbuzz.fibonacci.initial_value_note') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="fib_number">{{ __('fizzbuzz.fibonacci.number_label') }}</label>
                        <input type="number" id="fib_number" name="number" min="0" step="1"
                               value="{{ $fibInputNumber ?? '' }}" required
                               class="{{ $errors->has('number') ? 'input-error' : '' }}">
                        @if($errors->has('number'))
                            <div class="error-message">{{ $errors->first('number') }}</div>
                        @endif
                    </div>
                    <div class="form-actions">
                        <button type="submit">{{ __('fizzbuzz.fibonacci.calculate') }}</button>
                    </div>
                </form>
            </div>

            @if(isset($fibonacciResult))
            <div class="form-section">
                <h2>{{ __('fizzbuzz.fibonacci.result_title') }}</h2>
                <div class="result-list" id="fibonacciResults">
                    @foreach($fibonacciResult as $num)
                    <div class="result-list-item">{{ $num }}</div>
                    @endforeach
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-download" onclick="downloadFibonacciResults()">{{ __('fizzbuzz.fibonacci.download') }}</button>
                </div>
            </div>
            @endif
        </div>

        <!-- Tab Combine -->
        <div id="combine" class="tab-content {{ ($activeTab ?? 'fizzbuzz') === 'combine' ? 'active' : '' }}">
            <div class="form-section">
                <h2>{{ __('fizzbuzz.combine.title') }}</h2>
                @if($errors->has('number'))
                    <div class="error-message">{{ $errors->first('number') }}</div>
                @endif
                <form method="GET" action="{{ route('combine') }}" id="combineForm" onsubmit="return validateForm(this)">
                    @if(request()->has('lang'))
                        <input type="hidden" name="lang" value="{{ request()->get('lang') }}">
                    @endif
                    <div class="form-group">
                        <label>{{ __('fizzbuzz.combine.initial_value') }}</label>
                        <input type="number" name="start_x" placeholder="{{ __('fizzbuzz.combine.start_x') }}" value="{{ isset($combineStartX) ? $combineStartX : 0 }}">
                        <small style="color: #666; font-size: 0.875rem; display: block; margin-top: 0.25rem;">{{ __('fizzbuzz.combine.initial_value_note') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="combine_number">{{ __('fizzbuzz.combine.number_label') }}</label>
                        <input type="number" id="combine_number" name="number" min="0" step="1"
                               value="{{ $combineInputNumber ?? '' }}" required
                               class="{{ $errors->has('number') ? 'input-error' : '' }}">
                        @if($errors->has('number'))
                            <div class="error-message">{{ $errors->first('number') }}</div>
                        @endif
                    </div>
                    <div class="custom-rules">
                        <label>{{ __('fizzbuzz.combine.custom_rules') }}</label>
                        <div id="combineRulesContainer">
                            @if(isset($combineCustomRulesArray) && count($combineCustomRulesArray) > 0)
                                @foreach($combineCustomRulesArray as $index => $rule)
                                <div class="rule-input">
                                    <input type="number" name="custom_rules[{{ $index }}][number]" placeholder="{{ __('fizzbuzz.combine.number_placeholder') }}" min="1" value="{{ $rule['number'] }}">
                                    <input type="text" name="custom_rules[{{ $index }}][word]" placeholder="{{ __('fizzbuzz.combine.word_placeholder') }}" value="{{ $rule['word'] }}">
                                    <button type="button" onclick="this.parentElement.remove()">×</button>
                                </div>
                                @endforeach
                            @else
                                <div class="rule-input">
                                    <input type="number" name="custom_rules[0][number]" placeholder="{{ __('fizzbuzz.combine.number_placeholder') }}" min="1">
                                    <input type="text" name="custom_rules[0][word]" placeholder="{{ __('fizzbuzz.combine.word_placeholder') }}">
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addCombineRule()" style="margin-top: 0.5rem;">{{ __('fizzbuzz.combine.add_rule') }}</button>
                    </div>
                    <div class="form-actions">
                        <button type="submit">{{ __('fizzbuzz.combine.process') }}</button>
                    </div>
                </form>
            </div>

            @if(isset($combineResult))
            <div class="form-section">
                <h2>{{ __('fizzbuzz.combine.result_title') }}</h2>
                <div class="result-list" id="combineResults">
                    @foreach($combineResult as $item)
                    <div class="result-list-item {{ strtolower($item['output']) }} {{ ($item['is_word'] ?? false) ? 'word-result' : '' }}">
                        {{ $item['output'] }}
                    </div>
                    @endforeach
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-download" onclick="downloadCombineResults()">{{ __('fizzbuzz.combine.download') }}</button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        function changeLanguage(locale) {
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            url.searchParams.set('lang', locale);
            window.location.href = url.toString();
        }

        // Translations
        const translations = {
            showInstructions: @json(__('fizzbuzz.info_card.show_instructions')),
            hideInstructions: @json(__('fizzbuzz.info_card.hide_instructions')),
            invalidNumber: @json(__('fizzbuzz.validation.invalid_number')),
            downloadFizzBuzzTitle: @json(__('fizzbuzz.download.fizzbuzz_title')),
            downloadFizzBuzzSeparator: @json(__('fizzbuzz.download.fizzbuzz_separator')),
            downloadFibonacciTitle: @json(__('fizzbuzz.download.fibonacci_title')),
            downloadFibonacciSeparator: @json(__('fizzbuzz.download.fibonacci_separator')),
            downloadCombineTitle: @json(__('fizzbuzz.download.combine_title')),
            downloadCombineSeparator: @json(__('fizzbuzz.download.combine_separator')),
        };

        function toggleInfoCard() {
            const card = document.getElementById('infoCard');
            const button = card.querySelector('.toggle-info');
            card.classList.toggle('collapsed');
            button.textContent = card.classList.contains('collapsed') ? translations.showInstructions : translations.hideInstructions;
        }

        function validateForm(form) {
            const numberInput = form.querySelector('input[name="number"]');
            if (numberInput) {
                const value = parseInt(numberInput.value);
                if (isNaN(value) || value < 0) {
                    alert(translations.invalidNumber);
                    numberInput.focus();
                    return false;
                }
            }
            return true;
        }

        function showTab(tabName, buttonElement) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show the selected tab
            document.getElementById(tabName).classList.add('active');
            if (buttonElement) {
                buttonElement.classList.add('active');
            }
        }

        function addRule() {
            const container = document.getElementById('rulesContainer');
            const existingRules = container.querySelectorAll('.rule-input');
            const ruleCount = existingRules.length;
            const div = document.createElement('div');
            div.className = 'rule-input';
            div.innerHTML = `
                <input type="number" name="custom_rules[${ruleCount}][number]" placeholder="{{ __('fizzbuzz.fizzbuzz.number_placeholder') }}" min="1">
                <input type="text" name="custom_rules[${ruleCount}][word]" placeholder="{{ __('fizzbuzz.fizzbuzz.word_placeholder') }}">
                <button type="button" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }

        function addCombineRule() {
            const container = document.getElementById('combineRulesContainer');
            const existingRules = container.querySelectorAll('.rule-input');
            const ruleCount = existingRules.length;
            const div = document.createElement('div');
            div.className = 'rule-input';
            div.innerHTML = `
                <input type="number" name="custom_rules[${ruleCount}][number]" placeholder="{{ __('fizzbuzz.combine.number_placeholder') }}" min="1">
                <input type="text" name="custom_rules[${ruleCount}][word]" placeholder="{{ __('fizzbuzz.combine.word_placeholder') }}">
                <button type="button" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }

        function downloadTextFile(content, filename) {
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function downloadFizzBuzzResults() {
            const resultsContainer = document.getElementById('fizzbuzzResults');
            if (!resultsContainer) return;
            
            const items = resultsContainer.querySelectorAll('.result-item');
            let content = translations.downloadFizzBuzzTitle + '\n';
            content += translations.downloadFizzBuzzSeparator + '\n\n';
            
            items.forEach((item, index) => {
                content += item.textContent.trim();
                if (index < items.length - 1) {
                    content += ', ';
                }
                if ((index + 1) % 10 === 0) {
                    content += '\n';
                }
            });
            
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
            downloadTextFile(content, `fizzbuzz-results-${timestamp}.txt`);
        }

        function downloadFibonacciResults() {
            const resultsContainer = document.getElementById('fibonacciResults');
            if (!resultsContainer) return;
            
            const items = resultsContainer.querySelectorAll('.result-list-item');
            let content = translations.downloadFibonacciTitle + '\n';
            content += translations.downloadFibonacciSeparator + '\n\n';
            
            items.forEach((item, index) => {
                content += item.textContent.trim();
                if (index < items.length - 1) {
                    content += ', ';
                }
                if ((index + 1) % 10 === 0) {
                    content += '\n';
                }
            });
            
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
            downloadTextFile(content, `fibonacci-results-${timestamp}.txt`);
        }

        function downloadCombineResults() {
            const resultsContainer = document.getElementById('combineResults');
            if (!resultsContainer) return;
            
            const items = resultsContainer.querySelectorAll('.result-list-item');
            let content = translations.downloadCombineTitle + '\n';
            content += translations.downloadCombineSeparator + '\n\n';
            
            items.forEach((item, index) => {
                content += item.textContent.trim();
                if (index < items.length - 1) {
                    content += ', ';
                }
                if ((index + 1) % 10 === 0) {
                    content += '\n';
                }
            });
            
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
            downloadTextFile(content, `combine-results-${timestamp}.txt`);
        }
    </script>
</body>
</html>
