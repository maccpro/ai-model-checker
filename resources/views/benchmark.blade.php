<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Model Strength Checker & Benchmarking Suite</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        },
                        slate: {
                            850: '#151f32',
                            900: '#0f172a',
                            950: '#090d16',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Marked for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        pre code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }
        .prose pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 0.5rem 0;
            font-size: 0.875rem;
        }
        .prose code {
            color: #38bdf8;
            background-color: rgba(56, 189, 248, 0.1);
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
            font-size: 0.85em;
        }
        .prose p { margin-bottom: 0.5rem; line-height: 1.6; }
        .prose ul, .prose ol { margin-left: 1.25rem; margin-bottom: 0.5rem; list-style-type: disc; }
        .prose ol { list-style-type: decimal; }
    </style>
</head>
<body 
    x-data="aiModelTester()" 
    x-init="initApp()" 
    :class="darkMode ? 'dark bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-800'" 
    class="h-full flex flex-col font-sans transition-colors duration-200 antialiased selection:bg-brand-500 selection:text-white"
>
    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-40 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-brand-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                    <i data-lucide="cpu" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        AI Model Strength Checker
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800">Laravel 13</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Universal Endpoint & Benchmark Suite</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Saved Endpoints Button -->
                <button 
                    @click="showSavedModal = true" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-medium transition"
                    title="Manage Saved Endpoints"
                >
                    <i data-lucide="bookmark" class="w-4 h-4 text-indigo-500"></i>
                    <span>Saved Endpoints</span>
                    <span x-show="savedEndpoints.length > 0" class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300" x-text="savedEndpoints.length"></span>
                </button>

                <!-- History Button -->
                <button 
                    @click="openHistory()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-medium transition"
                    title="View Past Benchmarks"
                >
                    <i data-lucide="history" class="w-4 h-4 text-amber-500"></i>
                    <span>History</span>
                </button>

                <!-- Dark/Light Mode Toggle -->
                <button 
                    @click="toggleTheme()" 
                    class="p-2 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition"
                    title="Toggle Theme"
                >
                    <i x-show="!darkMode" data-lucide="moon" class="w-4 h-4"></i>
                    <i x-show="darkMode" data-lucide="sun" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        
        <!-- SECTION 1: Dynamic Connection & Model Discovery -->
        <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm transition">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <!-- Base URL Input -->
                <div class="flex-1 space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        AI Base URL <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </div>
                        <input 
                            type="url" 
                            x-model="baseUrl" 
                            placeholder="https://api.openai.com/v1, http://localhost:11434/v1, or any custom endpoint" 
                            class="block w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                        >
                    </div>
                </div>

                <!-- API Key Input -->
                <div class="flex-1 space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                            API Key <span class="text-slate-400 font-normal lowercase">(optional for local servers)</span>
                        </label>
                        <button 
                            type="button"
                            @click="showKey = !showKey" 
                            class="text-[11px] text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium"
                            x-text="showKey ? 'Hide Key' : 'Show Key'"
                        ></button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="key" class="w-4 h-4"></i>
                        </div>
                        <input 
                            :type="showKey ? 'text' : 'password'" 
                            x-model="apiKey" 
                            placeholder="sk-..." 
                            class="block w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                        >
                    </div>
                </div>

                <!-- Action Buttons: Fetch & Save -->
                <div class="flex items-center space-x-2 shrink-0">
                    <button 
                        @click="fetchModels()" 
                        :disabled="fetchingModels || !baseUrl"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl text-white bg-brand-600 hover:bg-brand-500 active:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-brand-500/20 transition"
                    >
                        <span x-show="fetchingModels" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                        <i x-show="!fetchingModels" data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span x-text="fetchingModels ? 'Fetching Models...' : 'Fetch Models'"></span>
                    </button>

                    <button 
                        @click="saveCurrentEndpointPrompt()" 
                        :disabled="!baseUrl"
                        class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition"
                        title="Save this Endpoint to Library"
                    >
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Fetch Status & Model Stats -->
            <div x-show="fetchError || models.length > 0" x-cloak class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between text-xs">
                <div x-show="fetchError" class="text-rose-500 flex items-center gap-1.5 font-medium">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span x-text="fetchError"></span>
                </div>
                <div x-show="models.length > 0" class="text-emerald-600 dark:text-emerald-400 flex items-center gap-2 font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="models.length + ' Models loaded from endpoint'"></span>
                    <span class="text-slate-400 text-[11px]" x-show="fetchLatencyMs">(Latency: <span x-text="fetchLatencyMs"></span>ms)</span>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Mode Selector & Model Selection -->
        <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm transition space-y-4">
            <!-- Mode Switcher Tabs -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center space-x-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                    <button 
                        @click="switchMode('playground')" 
                        :class="mode === 'playground' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                    >
                        <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
                        <span>Playground</span>
                    </button>
                    <button 
                        @click="switchMode('arena')" 
                        :class="mode === 'arena' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                    >
                        <i data-lucide="swords" class="w-3.5 h-3.5"></i>
                        <span>Side-by-Side Arena</span>
                    </button>
                    <button 
                        @click="switchMode('strength_suite')" 
                        :class="mode === 'strength_suite' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                    >
                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        <span>Strength Benchmark Suite</span>
                    </button>
                </div>

                <!-- Parameters Toggle & Quick Settings -->
                <div class="flex items-center space-x-3 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" x-model="streamEnabled" :disabled="mode === 'arena'" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Real-time Stream</span>
                    </label>
                    <button 
                        @click="showParams = !showParams" 
                        class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium"
                    >
                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                        <span x-text="showParams ? 'Hide Params' : 'Hyperparameters'"></span>
                    </button>
                </div>
            </div>

            <!-- Hyperparameters Drawer (Collapsible) -->
            <div x-show="showParams" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 text-xs">
                <div>
                    <label class="flex justify-between font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <span>Temperature</span>
                        <span x-text="temperature"></span>
                    </label>
                    <input type="range" min="0" max="2" step="0.1" x-model="temperature" class="w-full accent-brand-600">
                    <p class="text-[10px] text-slate-400 mt-0.5">Lower is more deterministic, higher is more creative.</p>
                </div>
                <div>
                    <label class="flex justify-between font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <span>Max Tokens</span>
                        <span x-text="maxTokens || 'Auto'"></span>
                    </label>
                    <input type="number" min="1" max="64000" x-model="maxTokens" placeholder="Optional (e.g. 2048)" class="w-full px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="flex justify-between font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <span>Top P</span>
                        <span x-text="topP"></span>
                    </label>
                    <input type="range" min="0" max="1" step="0.05" x-model="topP" class="w-full accent-brand-600">
                    <p class="text-[10px] text-slate-400 mt-0.5">Nucleus sampling threshold.</p>
                </div>
            </div>

            <!-- Model Selection Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Single Model or Model A -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <span x-show="mode === 'playground'">Select Model</span>
                        <span x-show="mode === 'arena'">Model A (Left)</span>
                        <span x-show="mode === 'strength_suite'">Target Model</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            x-model="selectedModel" 
                            class="block w-full py-2 pl-3 pr-8 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                        >
                            <option value="">-- Choose a Model --</option>
                            <template x-for="m in models" :key="m.id">
                                <option :value="m.id" x-text="m.id"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Model B (Only for Side-by-Side Arena) -->
                <div x-show="mode === 'arena'" class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Model B (Right) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            x-model="selectedModelB" 
                            class="block w-full py-2 pl-3 pr-8 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                        >
                            <option value="">-- Choose Model B to Compare --</option>
                            <template x-for="m in models" :key="'b-' + m.id">
                                <option :value="m.id" x-text="m.id"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Strength Suite Category Selector (Only for Suite mode) -->
                <div x-show="mode === 'strength_suite'" class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Benchmark Challenge <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        x-model="selectedSuiteKey" 
                        @change="loadSuitePreset()"
                        class="block w-full py-2 pl-3 pr-8 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
                    >
                        <template x-for="(suite, key) in strengthSuites" :key="key">
                            <option :value="key" x-text="suite.name + ' (' + suite.category + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Suite Description Banner -->
            <div x-show="mode === 'strength_suite' && strengthSuites[selectedSuiteKey]" x-cloak class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs flex items-start gap-2.5 text-amber-900 dark:text-amber-200">
                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <div>
                    <strong x-text="strengthSuites[selectedSuiteKey]?.name"></strong>: 
                    <span x-text="strengthSuites[selectedSuiteKey]?.description"></span>
                    <div class="mt-1 text-[11px] text-amber-700 dark:text-amber-300">
                        <strong>Expected Capability:</strong> <span x-text="strengthSuites[selectedSuiteKey]?.expected_trait"></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: Prompt Editor & Run Controls -->
        <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm transition space-y-4">
            <!-- System Prompt (Collapsible) -->
            <div x-data="{ openSystem: false }">
                <button 
                    type="button" 
                    @click="openSystem = !openSystem" 
                    class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 transition"
                >
                    <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                    <span>System Prompt</span>
                    <span class="text-slate-400 font-normal lowercase">(optional instructions)</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="openSystem ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openSystem" x-cloak class="mt-2">
                    <textarea 
                        x-model="systemPrompt" 
                        rows="2" 
                        placeholder="e.g. You are a senior software architect with deep knowledge in systems design..." 
                        class="w-full text-xs p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                    ></textarea>
                </div>
            </div>

            <!-- User Prompt Textarea -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs">
                    <label class="font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Prompt <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-slate-400 text-[11px]">Press <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px]">Ctrl + Enter</kbd> to run</span>
                </div>
                <textarea 
                    x-model="prompt" 
                    @keydown.ctrl.enter.prevent="executeTest()" 
                    rows="4" 
                    placeholder="Enter the prompt you want to test or benchmark..." 
                    class="w-full text-sm p-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 transition font-mono leading-relaxed"
                ></textarea>
            </div>

            <!-- Execution Action Bar -->
            <div class="flex items-center justify-between pt-2">
                <div class="text-xs text-slate-500">
                    <span x-show="isRunning" class="inline-flex items-center gap-2 text-brand-600 dark:text-brand-400 font-semibold animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-ping"></span>
                        <span x-text="runningMessage"></span>
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <button 
                        @click="clearResults()" 
                        x-show="hasResults" 
                        class="px-3 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition"
                    >
                        Clear Results
                    </button>

                    <button 
                        @click="executeTest()" 
                        :disabled="isRunning || !canExecute" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold rounded-xl text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-brand-500/20 transition transform"
                    >
                        <span x-show="isRunning" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                        <i x-show="!isRunning" data-lucide="play" class="w-4 h-4 fill-white"></i>
                        <span x-text="isRunning ? 'Running Test...' : (mode === 'arena' ? 'Launch Battle Arena' : (mode === 'strength_suite' ? 'Run Strength Test' : 'Execute Test'))"></span>
                    </button>
                </div>
            </div>
        </section>

        <!-- SECTION 4: Performance HUD & Real-Time Output -->
        <section x-show="hasResults || isRunning" x-cloak class="space-y-6">
            
            <!-- Output Container: Single vs Dual Split View -->
            <div :class="mode === 'arena' ? 'grid grid-cols-1 lg:grid-cols-2 gap-6' : 'space-y-6'">
                
                <!-- CARD A / PRIMARY RESULT -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col transition">
                    <!-- Model Header Bar -->
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-850/50">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300">
                                <span x-show="mode === 'arena'">Model A</span>
                                <span x-show="mode !== 'arena'">Result</span>
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-100" x-text="resultA.model_name || selectedModel"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="copyText(resultA.content)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs transition" title="Copy Text">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Metrics HUD Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-slate-100/60 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-center">
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">TTFT</div>
                            <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                                <span x-text="resultA.ttft_ms !== null && resultA.ttft_ms !== undefined ? resultA.ttft_ms + ' ms' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Speed (TPS)</div>
                            <div class="text-sm font-bold text-brand-600 dark:text-brand-400 font-mono">
                                <span x-text="resultA.tokens_per_second ? resultA.tokens_per_second + ' t/s' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Total Latency</div>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-mono">
                                <span x-text="resultA.total_duration_ms ? (resultA.total_duration_ms / 1000).toFixed(2) + ' s' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Tokens</div>
                            <div class="text-sm font-bold text-amber-600 dark:text-amber-400 font-mono">
                                <span x-text="resultA.total_tokens || resultA.completion_tokens || '--'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Output Content -->
                    <div class="p-5 flex-1 min-h-[160px] text-sm leading-relaxed overflow-y-auto">
                        <div x-show="resultA.error" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-600 dark:text-rose-400 text-xs">
                            <strong>Error:</strong> <span x-text="resultA.error"></span>
                        </div>
                        <div x-show="!resultA.error && resultA.content" class="prose dark:prose-invert max-w-none text-xs sm:text-sm" x-html="renderMarkdown(resultA.content)"></div>
                        <div x-show="!resultA.error && !resultA.content && isRunning" class="flex items-center justify-center py-12 text-slate-400 gap-2">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-brand-500 border-t-transparent rounded-full"></span>
                            <span class="text-xs">Generating response...</span>
                        </div>
                    </div>
                </div>

                <!-- CARD B (Only for Side-by-Side Arena) -->
                <div x-show="mode === 'arena'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col transition">
                    <!-- Model Header Bar -->
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-850/50">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">Model B</span>
                            <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-100" x-text="resultB.model_name || selectedModelB"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="copyText(resultB.content)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs transition" title="Copy Text">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Metrics HUD Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-slate-100/60 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-center">
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">TTFT</div>
                            <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                                <span x-text="resultB.ttft_ms !== null && resultB.ttft_ms !== undefined ? resultB.ttft_ms + ' ms' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Speed (TPS)</div>
                            <div class="text-sm font-bold text-brand-600 dark:text-brand-400 font-mono">
                                <span x-text="resultB.tokens_per_second ? resultB.tokens_per_second + ' t/s' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Total Latency</div>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-mono">
                                <span x-text="resultB.total_duration_ms ? (resultB.total_duration_ms / 1000).toFixed(2) + ' s' : '--'"></span>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-semibold text-slate-400">Tokens</div>
                            <div class="text-sm font-bold text-amber-600 dark:text-amber-400 font-mono">
                                <span x-text="resultB.total_tokens || resultB.completion_tokens || '--'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Output Content -->
                    <div class="p-5 flex-1 min-h-[160px] text-sm leading-relaxed overflow-y-auto">
                        <div x-show="resultB.error" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-600 dark:text-rose-400 text-xs">
                            <strong>Error:</strong> <span x-text="resultB.error"></span>
                        </div>
                        <div x-show="!resultB.error && resultB.content" class="prose dark:prose-invert max-w-none text-xs sm:text-sm" x-html="renderMarkdown(resultB.content)"></div>
                        <div x-show="!resultB.error && !resultB.content && isRunning" class="flex items-center justify-center py-12 text-slate-400 gap-2">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full"></span>
                            <span class="text-xs">Generating response...</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <!-- MODAL: Saved Endpoints Manager -->
    <div x-show="showSavedModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div @click.away="showSavedModal = false" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="bookmark" class="w-5 h-5 text-indigo-500"></i>
                    <span>Saved Endpoints</span>
                </h3>
                <button @click="showSavedModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- List of Saved Endpoints -->
            <div class="space-y-2 max-h-72 overflow-y-auto">
                <template x-if="savedEndpoints.length === 0">
                    <div class="text-center py-8 text-xs text-slate-400">
                        No saved endpoints yet. Use the "+" button next to Fetch Models to save any custom Base URL!
                    </div>
                </template>
                <template x-for="item in savedEndpoints" :key="item.id">
                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 flex items-center justify-between transition bg-slate-50/50 dark:bg-slate-850/50">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="item.name"></div>
                            <div class="text-[11px] text-slate-500 font-mono truncate max-w-[280px]" x-text="item.base_url"></div>
                            <div class="text-[10px] text-slate-400" x-text="item.masked_api_key || 'No key'"></div>
                        </div>
                        <div class="flex items-center space-x-1">
                            <button @click="loadSavedEndpoint(item)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-50 dark:bg-brand-950 text-brand-700 dark:text-brand-300 hover:bg-brand-100 transition">
                                Load
                            </button>
                            <button @click="deleteSavedEndpoint(item.id)" class="p-1 text-slate-400 hover:text-rose-500 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- MODAL: History Drawer -->
    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div @click.away="showHistoryModal = false" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-3xl w-full p-6 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-amber-500"></i>
                        <span>Benchmark History</span>
                    </h3>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="clearAllHistory()" x-show="historyRuns.length > 0" class="text-xs text-rose-500 hover:underline">
                        Clear All History
                    </button>
                    <button @click="showHistoryModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- History Runs List -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                <template x-if="historyRuns.length === 0">
                    <div class="text-center py-12 text-xs text-slate-400">
                        No benchmark runs recorded yet. Run a prompt or benchmark challenge to build history!
                    </div>
                </template>
                <template x-for="run in historyRuns" :key="run.id">
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850/50 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="run.mode === 'arena' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : (run.mode === 'strength_suite' ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300' : 'bg-brand-100 dark:bg-brand-950 text-brand-700 dark:text-brand-300')" x-text="run.mode"></span>
                                <span x-show="run.category" class="text-slate-400 text-[11px]" x-text="'(' + run.category + ')'"></span>
                                <span class="text-slate-400 text-[11px]" x-text="new Date(run.created_at).toLocaleString()"></span>
                            </div>
                            <button @click="deleteRun(run.id)" class="text-slate-400 hover:text-rose-500">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-300 font-mono truncate bg-white dark:bg-slate-900 p-2 rounded border border-slate-200/50 dark:border-slate-800" x-text="run.prompt"></div>
                        
                        <!-- Results inside this run -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            <template x-for="res in run.results" :key="res.id">
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 text-[11px] space-y-1">
                                    <div class="flex justify-between font-bold">
                                        <span class="font-mono text-slate-900 dark:text-white" x-text="res.model_name"></span>
                                        <span :class="res.status === 'success' ? 'text-emerald-500' : 'text-rose-500'" x-text="res.status"></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1 text-[10px] text-slate-500">
                                        <div>TTFT: <strong x-text="res.ttft_ms ? res.ttft_ms + 'ms' : '--'"></strong></div>
                                        <div>Speed: <strong x-text="res.tokens_per_second ? res.tokens_per_second + ' t/s' : '--'"></strong></div>
                                        <div>Tokens: <strong x-text="res.total_tokens || res.completion_tokens || '--'"></strong></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Alpine App Controller -->
    <script>
        function aiModelTester() {
            return {
                darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                baseUrl: localStorage.getItem('ai_base_url') || '',
                apiKey: localStorage.getItem('ai_api_key') || '',
                showKey: false,
                models: [],
                fetchingModels: false,
                fetchError: null,
                fetchLatencyMs: null,

                mode: 'playground', // 'playground', 'arena', 'strength_suite'
                selectedModel: '',
                selectedModelB: '',
                selectedSuiteKey: 'reasoning_math',
                strengthSuites: {},

                systemPrompt: '',
                prompt: 'Explain quantum computing in 2 simple sentences.',
                temperature: 0.7,
                maxTokens: '',
                topP: 1.0,
                streamEnabled: true,
                showParams: false,

                isRunning: false,
                runningMessage: '',
                hasResults: false,

                resultA: {
                    model_name: '',
                    content: '',
                    ttft_ms: null,
                    total_duration_ms: null,
                    tokens_per_second: null,
                    total_tokens: null,
                    error: null
                },
                resultB: {
                    model_name: '',
                    content: '',
                    ttft_ms: null,
                    total_duration_ms: null,
                    tokens_per_second: null,
                    total_tokens: null,
                    error: null
                },

                showSavedModal: false,
                savedEndpoints: [],
                showHistoryModal: false,
                historyRuns: [],

                initApp() {
                    this.applyTheme();
                    this.loadSavedEndpoints();
                    this.loadStrengthSuites();
                    if (this.baseUrl) {
                        this.fetchModels();
                    }
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                    this.applyTheme();
                    this.$nextTick(() => lucide.createIcons());
                },

                applyTheme() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },

                switchMode(newMode) {
                    this.mode = newMode;
                    if (newMode === 'strength_suite') {
                        this.loadSuitePreset();
                    }
                    this.$nextTick(() => lucide.createIcons());
                },

                async loadStrengthSuites() {
                    try {
                        const res = await fetch('/api/benchmark/suites');
                        const data = await res.json();
                        if (data.success) {
                            this.strengthSuites = data.suites;
                        }
                    } catch (e) {
                        console.error('Failed to load strength suites', e);
                    }
                },

                loadSuitePreset() {
                    const suite = this.strengthSuites[this.selectedSuiteKey];
                    if (suite) {
                        this.prompt = suite.prompt;
                        this.systemPrompt = suite.system_prompt || '';
                    }
                },

                async fetchModels() {
                    if (!this.baseUrl) return;
                    this.fetchingModels = true;
                    this.fetchError = null;
                    this.models = [];

                    localStorage.setItem('ai_base_url', this.baseUrl);
                    if (this.apiKey) localStorage.setItem('ai_api_key', this.apiKey);

                    try {
                        const res = await fetch('/api/models/fetch', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                base_url: this.baseUrl,
                                api_key: this.apiKey || null
                            })
                        });

                        const data = await res.json();
                        this.fetchLatencyMs = data.latency_ms;

                        if (data.success && data.models) {
                            this.models = data.models;
                            if (this.models.length > 0) {
                                if (!this.selectedModel || !this.models.some(m => m.id === this.selectedModel)) {
                                    this.selectedModel = this.models[0].id;
                                }
                                if (this.models.length > 1 && !this.selectedModelB) {
                                    this.selectedModelB = this.models[1].id;
                                }
                            }
                        } else {
                            this.fetchError = data.error || 'Failed to fetch models from endpoint.';
                        }
                    } catch (e) {
                        this.fetchError = e.message || 'Network error connecting to proxy.';
                    } finally {
                        this.fetchingModels = false;
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                async loadSavedEndpoints() {
                    try {
                        const res = await fetch('/api/endpoints');
                        const data = await res.json();
                        if (data.success) {
                            this.savedEndpoints = data.endpoints;
                        }
                    } catch (e) {
                        console.error('Failed to load saved endpoints', e);
                    }
                },

                async saveCurrentEndpointPrompt() {
                    const name = prompt('Enter a nickname for this endpoint:');
                    if (!name) return;

                    try {
                        const res = await fetch('/api/endpoints', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: name,
                                base_url: this.baseUrl,
                                api_key: this.apiKey || null
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.loadSavedEndpoints();
                            alert('Endpoint saved successfully!');
                        }
                    } catch (e) {
                        alert('Failed to save endpoint: ' + e.message);
                    }
                },

                loadSavedEndpoint(item) {
                    this.baseUrl = item.base_url;
                    this.apiKey = item.raw_api_key || '';
                    this.showSavedModal = false;
                    this.fetchModels();
                },

                async deleteSavedEndpoint(id) {
                    if (!confirm('Are you sure you want to remove this saved endpoint?')) return;
                    try {
                        await fetch('/api/endpoints/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        this.loadSavedEndpoints();
                    } catch (e) {
                        alert('Failed to delete: ' + e.message);
                    }
                },

                async openHistory() {
                    this.showHistoryModal = true;
                    try {
                        const res = await fetch('/api/benchmark/history');
                        const data = await res.json();
                        if (data.success) {
                            this.historyRuns = data.runs;
                        }
                    } catch (e) {
                        console.error('Failed to load history', e);
                    } finally {
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                async deleteRun(runId) {
                    try {
                        await fetch('/api/benchmark/history/' + runId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        this.historyRuns = this.historyRuns.filter(r => r.id !== runId);
                    } catch (e) {
                        console.error('Failed to delete run', e);
                    }
                },

                async clearAllHistory() {
                    if (!confirm('Clear all benchmark history?')) return;
                    try {
                        await fetch('/api/benchmark/history', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        this.historyRuns = [];
                    } catch (e) {
                        console.error('Failed to clear history', e);
                    }
                },

                get canExecute() {
                    if (!this.baseUrl || !this.prompt) return false;
                    if (this.mode === 'arena') {
                        return this.selectedModel && this.selectedModelB;
                    }
                    return !!this.selectedModel;
                },

                clearResults() {
                    this.hasResults = false;
                    this.resultA = { model_name: '', content: '', ttft_ms: null, total_duration_ms: null, tokens_per_second: null, total_tokens: null, error: null };
                    this.resultB = { model_name: '', content: '', ttft_ms: null, total_duration_ms: null, tokens_per_second: null, total_tokens: null, error: null };
                },

                async executeTest() {
                    if (!this.canExecute || this.isRunning) return;

                    this.isRunning = true;
                    this.hasResults = true;
                    this.runningMessage = 'Contacting AI endpoint...';

                    // Reset previous outputs
                    this.resultA = { model_name: this.selectedModel, content: '', ttft_ms: null, total_duration_ms: null, tokens_per_second: null, total_tokens: null, error: null };
                    this.resultB = { model_name: this.selectedModelB, content: '', ttft_ms: null, total_duration_ms: null, tokens_per_second: null, total_tokens: null, error: null };

                    const modelsToRun = this.mode === 'arena' 
                        ? [this.selectedModel, this.selectedModelB] 
                        : [this.selectedModel];

                    // If single model in playground with streaming enabled
                    if (this.mode === 'playground' && this.streamEnabled) {
                        await this.executeStream(this.selectedModel);
                        this.isRunning = false;
                        return;
                    }

                    // Standard execution for Arena / Strength Suite / Non-stream
                    try {
                        const res = await fetch('/api/benchmark/run', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                base_url: this.baseUrl,
                                api_key: this.apiKey || null,
                                models: modelsToRun,
                                prompt: this.prompt,
                                system_prompt: this.systemPrompt || null,
                                temperature: parseFloat(this.temperature),
                                max_tokens: this.maxTokens ? parseInt(this.maxTokens) : null,
                                top_p: parseFloat(this.topP),
                                mode: this.mode,
                                category: this.mode === 'strength_suite' ? this.strengthSuites[this.selectedSuiteKey]?.category : null
                            })
                        });

                        const data = await res.json();
                        if (data.success && data.data?.results) {
                            const resList = data.data.results;
                            if (resList[0]) {
                                this.resultA = {
                                    model_name: resList[0].model_name,
                                    content: resList[0].content,
                                    ttft_ms: resList[0].ttft_ms,
                                    total_duration_ms: resList[0].total_duration_ms,
                                    tokens_per_second: resList[0].tokens_per_second,
                                    total_tokens: resList[0].total_tokens,
                                    error: resList[0].error
                                };
                            }
                            if (resList[1]) {
                                this.resultB = {
                                    model_name: resList[1].model_name,
                                    content: resList[1].content,
                                    ttft_ms: resList[1].ttft_ms,
                                    total_duration_ms: resList[1].total_duration_ms,
                                    tokens_per_second: resList[1].tokens_per_second,
                                    total_tokens: resList[1].total_tokens,
                                    error: resList[1].error
                                };
                            }
                        } else {
                            this.resultA.error = data.message || 'Execution failed.';
                        }
                    } catch (e) {
                        this.resultA.error = e.message || 'Network error.';
                    } finally {
                        this.isRunning = false;
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                async executeStream(modelName) {
                    const startTime = performance.now();
                    let firstChunk = true;

                    try {
                        const response = await fetch('/api/benchmark/stream', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'text/event-stream'
                            },
                            body: JSON.stringify({
                                base_url: this.baseUrl,
                                api_key: this.apiKey || null,
                                models: [modelName],
                                prompt: this.prompt,
                                system_prompt: this.systemPrompt || null,
                                temperature: parseFloat(this.temperature),
                                max_tokens: this.maxTokens ? parseInt(this.maxTokens) : null,
                                top_p: parseFloat(this.topP),
                                mode: 'playground'
                            })
                        });

                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        let accumulatedText = '';
                        let tokenCount = 0;

                        while (true) {
                            const { done, value } = await reader.read();
                            if (done) break;

                            const chunk = decoder.decode(value, { stream: true });
                            const lines = chunk.split('\n');

                            for (const line of lines) {
                                if (line.startsWith('data:')) {
                                    const dataStr = line.slice(5).trim();
                                    if (dataStr === '[DONE]') continue;
                                    try {
                                        const parsed = JSON.parse(dataStr);
                                        const delta = parsed.choices?.[0]?.delta?.content;
                                        if (delta) {
                                            if (firstChunk) {
                                                this.resultA.ttft_ms = Math.round(performance.now() - startTime);
                                                firstChunk = false;
                                            }
                                            accumulatedText += delta;
                                            tokenCount++;
                                            this.resultA.content = accumulatedText;
                                        }
                                    } catch (_) {}
                                }
                            }
                        }

                        const durationMs = Math.round(performance.now() - startTime);
                        this.resultA.total_duration_ms = durationMs;
                        this.resultA.total_tokens = tokenCount || Math.ceil(accumulatedText.length / 4);
                        const durSec = Math.max(durationMs / 1000, 0.001);
                        this.resultA.tokens_per_second = parseFloat((this.resultA.total_tokens / durSec).toFixed(2));

                    } catch (e) {
                        this.resultA.error = e.message || 'Streaming failed';
                    } finally {
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                renderMarkdown(content) {
                    if (!content) return '';
                    try {
                        return marked.parse(content);
                    } catch (e) {
                        return content;
                    }
                },

                copyText(text) {
                    if (!text) return;
                    navigator.clipboard.writeText(text);
                    alert('Copied to clipboard!');
                }
            }
        }
    </script>
</body>
</html>
