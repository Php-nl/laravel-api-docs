<div x-data="{ 
        rightTab: 'try', 
        snippetLang: 'curl',
        darkMode: localStorage.getItem('apiDocDarkMode') === 'true'
     }" 
     x-init="$watch('darkMode', val => localStorage.setItem('apiDocDarkMode', val))"
     :class="{ 'dark': darkMode }"
     class="flex h-screen overflow-hidden font-sans antialiased text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-900 transition-colors duration-200">
    
    <!-- Sidebar -->
    <div class="w-72 flex-shrink-0 border-r border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 flex flex-col h-full z-20 relative transition-colors duration-200">
        <div class="p-5 border-b border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[var(--primary-color)] to-indigo-700 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h1 class="font-extrabold text-[15px] tracking-tight text-slate-800 dark:text-slate-200">{{ config('laravel-api-doc.ui.title') }}</h1>
            </div>
            <button @click="darkMode = !darkMode" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md hover:bg-slate-200/50 dark:hover:bg-slate-700/50 transition-colors">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </button>
        </div>
        <div class="px-5 py-4">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400 group-focus-within:text-[var(--primary-color)] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.150ms="search" type="text" placeholder="Search API..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 border text-sm rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]/50 focus:border-[var(--primary-color)] transition-all placeholder-slate-400 text-slate-800 dark:text-slate-200">
            </div>
            <div class="mt-4">
                <a href="{{ route('laravel-api-doc.json') }}" target="_blank" class="w-full flex items-center justify-center px-4 py-2 border border-slate-200 dark:border-slate-700 shadow-sm text-xs font-semibold rounded-md text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-[var(--primary-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    OpenAPI 3.1.0 JSON
                </a>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
            @foreach($this->groups as $group => $endpoints)
                <div class="mb-6" x-data="{ expanded: {{ collect($endpoints)->contains('id', $selectedId) || $search !== '' ? 'true' : 'false' }} }">
                    <button @click="expanded = !expanded" class="w-full px-5 mb-2 focus:outline-none group/toggle">
                        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center group-hover/toggle:text-gray-600 transition-colors">
                            <span class="bg-gray-200 h-px flex-1 mr-2 group-hover/toggle:bg-gray-300 transition-colors"></span>
                            <span>{{ $group }}</span>
                            <span class="bg-gray-200 h-px flex-1 ml-2 mr-2 group-hover/toggle:bg-gray-300 transition-colors"></span>
                            <svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </h2>
                    </button>
                    <div class="space-y-0.5 mt-3" x-show="expanded" x-collapse x-cloak>
                        @foreach($endpoints as $endpoint)
                            <button wire:click="selectEndpoint('{{ $endpoint['id'] }}')" class="w-full text-left px-5 py-2.5 hover:bg-gray-100/80 transition-all group flex items-start space-x-3 border-l-2 {{ $selectedId === $endpoint['id'] ? 'bg-blue-50/50 border-[var(--primary-color)]' : 'border-transparent' }}">
                                <div class="mt-0.5 w-14 flex-shrink-0">
                                    <x-api-doc::badge :method="$endpoint['methods'][0]" class="text-[10px] px-1.5 py-0.5 rounded shadow-sm opacity-90 group-hover:opacity-100 {{ $selectedId === $endpoint['id'] ? 'opacity-100 ring-1 ring-offset-1 ring-[var(--primary-color)]/20' : '' }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate flex items-center {{ $selectedId === $endpoint['id'] ? 'text-[var(--primary-color)]' : 'text-gray-700 group-hover:text-gray-900' }}">
                                        {{ $endpoint['name'] ?: $endpoint['uri'] }}
                                        @if($endpoint['auth_required'] ?? false)
                                            <svg class="w-3.5 h-3.5 ml-1.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        @endif
                                    </div>
                                    @if($endpoint['name'])
                                        <div class="text-xs text-gray-500 font-mono truncate mt-1 group-hover:text-gray-600">{{ $endpoint['uri'] }}</div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Schemas Section -->
            @if(count($this->schemas) > 0)
                <div class="mb-6 mt-8" x-data="{ expanded: {{ $selectedSchemaId || $search !== '' ? 'true' : 'false' }} }">
                    <button @click="expanded = !expanded" class="w-full px-5 mb-2 focus:outline-none group/toggle">
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center group-hover/toggle:text-slate-700 transition-colors">
                            <span class="bg-slate-200 h-px flex-1 mr-2 group-hover/toggle:bg-slate-300 transition-colors"></span>
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>Schemas</span>
                            <span class="bg-slate-200 h-px flex-1 ml-2 mr-2 group-hover/toggle:bg-slate-300 transition-colors"></span>
                            <svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </h2>
                    </button>
                    <div class="space-y-0.5 mt-3" x-show="expanded" x-collapse x-cloak>
                        @foreach($this->schemas as $name => $schema)
                            @if($search === '' || str_contains(strtolower($name), strtolower($search)))
                                <button wire:click="selectSchema('{{ $name }}')" class="w-full text-left px-5 py-2.5 hover:bg-slate-100/80 transition-all group flex items-center space-x-3 border-l-2 {{ $selectedSchemaId === $name ? 'bg-[var(--primary-color)]/10 border-[var(--primary-color)]' : 'border-transparent' }}">
                                    <svg class="w-4 h-4 {{ $selectedSchemaId === $name ? 'text-[var(--primary-color)]' : 'text-slate-400 group-hover:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-semibold truncate flex items-center {{ $selectedSchemaId === $name ? 'text-[var(--primary-color)]' : 'text-slate-700 group-hover:text-slate-900' }}">
                                            {{ $name }}
                                        </div>
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content Split (Middle and Right) -->
    <div class="flex-1 flex overflow-hidden bg-white dark:bg-slate-900 transition-colors duration-200">
        @if($this->selectedSchema)
            <!-- Schema Full View -->
            <div class="flex-1 overflow-y-auto w-full bg-slate-50/30 dark:bg-slate-900/50 transition-colors duration-200">
                <div class="p-10 lg:p-14 max-w-5xl mx-auto">
                    <div class="mb-10 flex items-center">
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center mr-5 shadow-sm border border-indigo-100 dark:border-indigo-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">{{ $selectedSchemaId }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Resource Schema Definition</p>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-xl border border-slate-800 flex flex-col lg:flex-row min-h-[500px]">
                        <!-- Schema Tree -->
                        <div class="flex-1 p-8 lg:border-r border-slate-800">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Properties
                            </h3>
                            <x-api-doc::schema-tree :schema="$this->selectedSchema" />
                        </div>
                        
                        <!-- Example JSON -->
                        <div class="flex-1 bg-black/40 p-8">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                Example Payload
                            </h3>
                            @php
                                $generateExample = function($schema) use (&$generateExample) {
                                    if (isset($schema['example'])) return $schema['example'];
                                    if ($schema['type'] === 'array' && isset($schema['items'])) {
                                        return [$generateExample($schema['items'])];
                                    }
                                    if ($schema['type'] === 'object' && isset($schema['properties'])) {
                                        $obj = [];
                                        foreach ($schema['properties'] as $k => $v) {
                                            $obj[$k] = $generateExample($v);
                                        }
                                        return $obj;
                                    }
                                    return null;
                                };
                                $exampleData = $generateExample($this->selectedSchema);
                            @endphp
                            <pre class="font-mono text-[13px] text-slate-300 overflow-x-auto"><code>{!! json_encode($exampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($this->selectedEndpoint)
            <!-- Middle Column: Details -->
            <div class="flex-1 overflow-y-auto w-[40%] min-w-[500px] border-r border-slate-200 dark:border-slate-800 transition-colors duration-200">
                <div class="p-10 lg:p-14 max-w-3xl xl:max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center">
                            {{ $this->selectedEndpoint['name'] ?? $this->selectedEndpoint['uri'] }}
                            @if($this->selectedEndpoint['auth_required'] ?? false)
                                <svg class="w-6 h-6 ml-3 text-slate-400 dark:text-slate-500 hover:text-slate-500 dark:hover:text-slate-400 transition-colors" title="Authentication Required" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            @endif
                        </h2>
                        
                        <div class="mt-6 flex items-center space-x-4">
                            @foreach($this->selectedEndpoint['methods'] as $method)
                                <x-api-doc::badge :method="$method" class="px-3 py-1.5 text-sm font-semibold shadow-sm rounded-md" />
                            @endforeach
                            <div class="flex-1 flex items-center bg-slate-50 dark:bg-slate-800/50 rounded-md px-4 py-2.5 border border-slate-200 dark:border-slate-700/50 shadow-sm transition-colors duration-200">
                                <span class="text-slate-400 dark:text-slate-500 mr-1 select-none font-mono text-sm">{{ rtrim(url('/'), '/') }}</span>
                                <span class="text-slate-900 dark:text-slate-200 font-mono text-sm font-semibold">{{ str_starts_with($this->selectedEndpoint['uri'], '/') ? $this->selectedEndpoint['uri'] : '/' . $this->selectedEndpoint['uri'] }}</span>
                            </div>
                        </div>
                    </div>

                    @if($this->selectedEndpoint['description'])
                        <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 mb-12 border-l-4 border-slate-200 dark:border-slate-700 pl-4 py-1">
                            {!! \Illuminate\Support\Str::markdown($this->selectedEndpoint['description']) !!}
                        </div>
                    @endif

                    @php
                        $params = collect($this->selectedEndpoint['parameters'] ?? []);
                        $pathParams = $params->where('in', 'path');
                        $queryParams = $params->where('in', 'query');
                        $bodyParams = $params->where('in', 'body');
                    @endphp

                    @if($params->isNotEmpty())
                        <div class="mt-12 space-y-10">
                            @foreach([
                                'Path Variables' => $pathParams,
                                'Query Parameters' => $queryParams,
                                'Body Data' => $bodyParams
                            ] as $sectionTitle => $sectionParams)
                                @if($sectionParams->isNotEmpty())
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 pb-2 border-b border-slate-200 dark:border-slate-800 flex items-center transition-colors duration-200">
                                            @if($sectionTitle === 'Path Variables')
                                                <svg class="w-5 h-5 mr-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                            @elseif($sectionTitle === 'Query Parameters')
                                                <svg class="w-5 h-5 mr-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @else
                                                <svg class="w-5 h-5 mr-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                            @endif
                                            {{ $sectionTitle }}
                                        </h3>
                                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm transition-colors duration-200">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 transition-colors duration-200">
                                                    <tr>
                                                        <th class="px-5 py-3.5 font-semibold text-slate-700 dark:text-slate-300 w-1/3">Name</th>
                                                        <th class="px-5 py-3.5 font-semibold text-slate-700 dark:text-slate-300 w-1/4">Type</th>
                                                        <th class="px-5 py-3.5 font-semibold text-slate-700 dark:text-slate-300">Description & Rules</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 bg-white dark:bg-slate-900 transition-colors duration-200">
                                                    @foreach($sectionParams as $parameter)
                                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors group">
                                                        <td class="px-5 py-4 align-top">
                                                            <div class="font-mono text-slate-900 dark:text-slate-200 font-semibold flex items-center">
                                                                @php
                                                                    $parts = explode('.', $parameter['name']);
                                                                    $lastName = array_pop($parts);
                                                                    $prefix = !empty($parts) ? implode('.', $parts) . '.' : '';
                                                                    $dotCount = count($parts);
                                                                @endphp
                                                                
                                                                <div class="flex items-center" style="{{ $dotCount > 0 ? 'margin-left: ' . ($dotCount * 1.25) . 'rem;' : '' }}">
                                                                    @if($dotCount > 0)
                                                                        <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v10a2 2 0 002 2h4"></path></svg>
                                                                    @endif
                                                                    <span>
                                                                        @if($prefix)
                                                                            <span class="text-slate-400 dark:text-slate-500 font-normal">{{ $prefix }}</span>
                                                                        @endif
                                                                        {{ $lastName }}
                                                                    </span>
                                                                </div>

                                                                @if($parameter['required'])
                                                                    <span class="text-red-500 ml-2.5 text-[10px] bg-red-50 dark:bg-red-500/10 px-1.5 py-0.5 rounded border border-red-100 dark:border-red-500/20 uppercase tracking-widest font-sans" title="Required">req</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-4 text-emerald-600 dark:text-emerald-400 font-mono text-xs align-top pt-4.5">{{ $parameter['type'] }}</td>
                                                        <td class="px-5 py-4 text-slate-600 dark:text-slate-400 align-top">
                                                            <div class="prose prose-sm prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400">
                                                                <div class="mt-2 text-xs">
                                                                    @if(isset($parameter['enumValues']) && is_array($parameter['enumValues']) && count($parameter['enumValues']) > 0)
                                                                        <div class="font-semibold text-slate-700 mb-1">Accepted Values:</div>
                                                                        <div class="flex flex-wrap gap-1.5">
                                                                            @foreach($parameter['enumValues'] as $val)
                                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 font-mono">{{ $val }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    @elseif($parameter['description'])
                                                                        <span class="text-slate-500">{{ $parameter['description'] }}</span>
                                                                    @else
                                                                        <span class="text-slate-400 italic">No description provided.</span>
                                                                    @endif
                                                                </div>
                                                                @if(!empty($parameter['rules']))
                                                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                                                        @foreach($parameter['rules'] as $rule)
                                                                            @if($rule !== 'required')
                                                                                <span class="bg-gray-100 text-gray-600 text-[10px] font-mono px-1.5 py-0.5 rounded border border-gray-200">{{ $rule }}</span>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    
                    @if(!empty($this->selectedEndpoint['responses']))
                        <div class="mt-12 mb-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Responses
                            </h3>
                            <div class="space-y-4">
                                @foreach($this->selectedEndpoint['responses'] as $response)
                                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm" x-data="{ open: true }">
                                        <div @click="open = !open" class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-colors">
                                            <div class="flex items-center">
                                                <span class="px-2 py-0.5 text-xs font-bold rounded-md shadow-sm border {{ $response['status'] >= 200 && $response['status'] < 300 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($response['status'] >= 400 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-gray-100 text-gray-700 border-gray-200') }}">
                                                    {{ $response['status'] }}
                                                </span>
                                                <span class="ml-3 text-sm font-semibold text-gray-700">{{ $response['description'] }}</span>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                        @if(!empty($response['schema']))
                                            <div class="bg-slate-900 overflow-hidden" x-show="open" x-collapse>
                                                <div x-data="{ responseTab: 'schema' }" class="flex flex-col">
                                                    <!-- Tabs Header -->
                                                    <div class="flex items-center px-4 pt-3 border-b border-slate-800 bg-slate-900/50">
                                                        <button @click="responseTab = 'schema'" :class="responseTab === 'schema' ? 'text-white border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-300'" class="px-4 py-2 text-[13px] font-bold tracking-wider uppercase transition-colors focus:outline-none -mb-px">
                                                            Schema
                                                        </button>
                                                        <button @click="responseTab = 'example'" :class="responseTab === 'example' ? 'text-white border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-300'" class="px-4 py-2 text-[13px] font-bold tracking-wider uppercase transition-colors focus:outline-none -mb-px">
                                                            Example
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Tab Contents -->
                                                    <div class="p-5 overflow-x-auto text-[13px] min-h-[150px]">
                                                        <!-- Schema Tree -->
                                                        <div x-show="responseTab === 'schema'" class="w-full">
                                                            <x-api-doc::schema-tree :schema="$response['schema']" />
                                                        </div>
                                                        
                                                        <!-- Example JSON -->
                                                        <div x-show="responseTab === 'example'" x-cloak class="w-full">
                                                            @php
                                                                $generateExample = function($schema) use (&$generateExample) {
                                                                    if (isset($schema['example'])) return $schema['example'];
                                                                    if ($schema['type'] === 'array' && isset($schema['items'])) {
                                                                        return [$generateExample($schema['items'])];
                                                                    }
                                                                    if ($schema['type'] === 'object' && isset($schema['properties'])) {
                                                                        $obj = [];
                                                                        foreach ($schema['properties'] as $k => $v) {
                                                                            $obj[$k] = $generateExample($v);
                                                                        }
                                                                        return $obj;
                                                                    }
                                                                    return null;
                                                                };
                                                                $exampleData = $generateExample($response['schema']);
                                                            @endphp
                                                            <pre class="font-mono text-slate-300 p-4 bg-black/40 rounded-lg border border-white/5 overflow-x-auto"><code>{!! json_encode($exampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Try It Out -->
            <div class="w-[45%] lg:w-[480px] xl:w-[500px] flex-shrink-0 bg-[#0f111a] overflow-y-auto border-l border-slate-800 flex flex-col shadow-2xl z-20" x-data="{ rightTab: 'try', snippetLang: 'curl' }">
                <div class="p-8">
                    <div class="flex items-center space-x-6 mb-6 pb-px border-b border-white/10 font-semibold text-sm">
                        <button @click="rightTab = 'try'" :class="rightTab === 'try' ? 'text-white border-b-2 border-[var(--primary-color)]' : 'text-slate-500 hover:text-slate-300'" class="pb-3 -mb-[2px] transition-colors focus:outline-none flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Try it out
                        </button>
                        <button @click="rightTab = 'code'" :class="rightTab === 'code' ? 'text-white border-b-2 border-[var(--primary-color)]' : 'text-slate-500 hover:text-slate-300'" class="pb-3 -mb-[2px] transition-colors focus:outline-none flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            Code Snippet
                        </button>
                    </div>

                    <div class="space-y-6" x-show="rightTab === 'try'">
                        <!-- Authentication Toggle -->
                        <div class="bg-slate-800/40 rounded-xl p-5 border border-slate-700/50 shadow-inner">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Authentication
                                </h4>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="useAuth" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--primary-color)] shadow-inner"></div>
                                </label>
                            </div>
                            
                            @if($useAuth)
                                <div class="mt-4 p-3 bg-slate-900/50 rounded-lg border border-slate-700/50">
                                    @if($globalAuthMethod === 'none')
                                        <div class="flex items-start text-amber-400 text-xs">
                                            <svg class="w-4 h-4 mr-1.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <span>No global authentication configured. Deselect this endpoint to configure it on the dashboard.</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-300 flex items-center">
                                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                                Using Global <strong class="ml-1 text-white">{{ strtoupper(str_replace('_', ' ', $globalAuthMethod)) }}</strong>
                                            </span>
                                            <button wire:click="selectEndpoint('')" class="text-[10px] text-[var(--primary-color)] hover:underline uppercase tracking-wider font-semibold">Edit</button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Parameters Form -->
                        @if(!empty($this->selectedEndpoint['parameters']))
                            <div class="bg-slate-800/40 rounded-xl p-5 border border-slate-700/50 shadow-inner">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Variables</h4>
                                </div>
                                <div class="space-y-5">
                                    @foreach($this->selectedEndpoint['parameters'] as $parameter)
                                        <x-api-doc::input 
                                            wire:model.defer="tryItOutForm.{{ $parameter['in'] }}.{{ $parameter['name'] }}"
                                            theme="dark"
                                            :label="$parameter['name']" 
                                            :required="$parameter['required']" 
                                            :type-hint="$parameter['type']"
                                            :enumValues="$parameter['enumValues'] ?? null" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-2">
                            <button wire:click="runRequest" wire:loading.attr="disabled" class="w-full flex items-center justify-center space-x-2 py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-[var(--primary-color)] hover:bg-opacity-90 hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#0f111a] focus:ring-[var(--primary-color)] transition-all">
                                <svg wire:loading.remove wire:target="runRequest" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <svg wire:loading wire:target="runRequest" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span><span wire:loading.remove wire:target="runRequest">Send Request</span><span wire:loading wire:target="runRequest">Sending...</span></span>
                            </button>
                        </div>

                        @if($response)
                            <x-api-doc::response :response="$response" />
                        @endif
                    </div>
                    
                    <!-- Code Snippets Tab -->
                    <div class="space-y-6" x-show="rightTab === 'code'" x-cloak>
                        @php
                            $smMethod = $this->selectedEndpoint['methods'][0] ?? 'GET';
                            $smUri = str_starts_with($this->selectedEndpoint['uri'], '/') ? $this->selectedEndpoint['uri'] : '/' . $this->selectedEndpoint['uri'];
                            $smUrl = rtrim(url('/'), '/') . $smUri;
                        @endphp
                        
                        <div class="flex space-x-2">
                            <button @click="snippetLang = 'curl'" :class="snippetLang === 'curl' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">cURL</button>
                            <button @click="snippetLang = 'javascript'" :class="snippetLang === 'javascript' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">JavaScript</button>
                            <button @click="snippetLang = 'php'" :class="snippetLang === 'php' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">PHP</button>
                        </div>
                        
                        <div class="bg-[#0b0f19] rounded-xl border border-slate-800 p-4 font-mono text-[13px] text-slate-300 overflow-x-auto shadow-inner relative group/copy">
                            <button x-on:click="navigator.clipboard.writeText($refs[snippetLang].innerText)" class="absolute top-3 right-3 p-1.5 bg-slate-800 text-slate-400 hover:text-white rounded opacity-0 group-hover/copy:opacity-100 transition-opacity" title="Copy to clipboard">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <!-- cURL -->
                            <div x-show="snippetLang === 'curl'" x-ref="curl">
<pre>curl --request {{ $smMethod }} \
  --url {{ $smUrl }} \
  --header 'Accept: application/json'</pre>
                            </div>
                            
                            <!-- JavaScript -->
                            <div x-show="snippetLang === 'javascript'" x-ref="javascript">
<pre>fetch('{{ $smUrl }}', {
  method: '{{ $smMethod }}',
  headers: {
    'Accept': 'application/json'
  }
})
  .then(response => response.json())
  .then(response => console.log(response))
  .catch(err => console.error(err));</pre>
                            </div>
                            
                            <!-- PHP -->
                            <div x-show="snippetLang === 'php'" x-ref="php">
<pre>$response = Http::withHeaders([
    'Accept' => 'application/json',
])->{{ strtolower($smMethod) }}('{{ $smUrl }}');

return $response->json();</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Dashboard / Welcome Screen -->
            <div class="flex-1 flex flex-col items-center bg-gray-50/50 text-gray-800 border-l border-gray-100 overflow-y-auto w-full">
                <div class="max-w-4xl w-full p-10 lg:p-14 mt-8">
                    <div class="text-center mb-14 animate-fade-in-up">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white text-[var(--primary-color)] rounded-[2rem] mb-6 shadow-xl border border-gray-100 ring-1 ring-gray-900/5 rotate-3 hover:rotate-0 transition-all duration-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        </div>
                        <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-4">{{ config('laravel-api-doc.ui.title') }}</h2>
                        <p class="text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">Welcome to the interactive API documentation. Explore all available endpoints, required parameters, and test API requests live from this dashboard.</p>
                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('laravel-api-doc.json') }}" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent shadow-md text-sm font-semibold rounded-full text-white bg-slate-900 dark:bg-indigo-600 hover:bg-slate-800 dark:hover:bg-indigo-500 transition-colors">
                                <svg class="w-5 h-5 mr-2 text-[var(--primary-color)] dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                OpenAPI Specification
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1">
                            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </div>
                            <span class="text-3xl font-bold text-slate-900 dark:text-white mb-1">{{ count($this->endpoints) }}</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Endpoints</span>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1" style="transition-delay: 50ms;">
                            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <span class="text-3xl font-bold text-slate-900 dark:text-white mb-1">{{ count($this->groups) }}</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Groups</span>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1" style="transition-delay: 100ms;">
                            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            </div>
                            <span class="text-xl font-bold text-slate-900 dark:text-white mb-1 break-all truncate w-full">{{ parse_url(url('/'), PHP_URL_HOST) ?? 'Local' }}</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">Environment</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 lg:p-10 shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden transition-colors">
                        <div class="absolute top-0 left-0 w-2 h-full bg-[var(--primary-color)]"></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center">
                            Getting Started
                        </h3>
                        <div class="space-y-5">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-50 dark:bg-green-500/10 text-green-500 dark:text-green-400 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Explore Navigation</h4>
                                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">Navigate using the sidebar on the left to explore available endpoints. The API is organized in logical groups based on domains.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Authenticate Requests</h4>
                                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">If an endpoint requires authentication, you can append your <code class="bg-gray-100 dark:bg-slate-800 text-[var(--primary-color)] px-1.5 py-0.5 rounded font-mono text-xs border border-gray-200 dark:border-slate-700">Bearer Token</code> using the input inside the <strong class="text-slate-900 dark:text-slate-200">"Try it out"</strong> panel on the right side.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-500 dark:text-purple-400 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Live Testing</h4>
                                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">Most endpoints can be executed straight from this dashboard to check responses live in your current environment by filling in required parameters and clicking "Send Request".</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Configuration -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 lg:p-10 shadow-sm border border-slate-100 dark:border-slate-800 mt-8 relative overflow-hidden transition-colors">
                        <div class="absolute top-0 left-0 w-2 h-full bg-slate-800 dark:bg-slate-700"></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-800 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Security & Authentication
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 pl-8">Configure global authentication credentials to automatically apply them to requests in the "Try it out" panel.</p>

                        <div class="pl-8">
                            <div class="flex flex-wrap items-center gap-6 mb-8">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="none" class="w-4 h-4 text-[var(--primary-color)] border-slate-300 dark:border-slate-600 focus:ring-[var(--primary-color)] cursor-pointer dark:bg-slate-800">
                                    <span class="ml-2 text-sm text-slate-700 dark:text-slate-300 font-medium group-hover:text-slate-900 dark:group-hover:text-white transition-colors">None</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="bearer" class="w-4 h-4 text-[var(--primary-color)] border-slate-300 dark:border-slate-600 focus:ring-[var(--primary-color)] cursor-pointer dark:bg-slate-800">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-gray-900 transition-colors">Bearer Token</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="basic" class="w-4 h-4 text-[var(--primary-color)] border-gray-300 focus:ring-[var(--primary-color)] cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-gray-900 transition-colors">Basic Auth</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="api_key" class="w-4 h-4 text-[var(--primary-color)] border-gray-300 focus:ring-[var(--primary-color)] cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-gray-900 transition-colors">API Key</span>
                                </label>
                            </div>

                            @if($globalAuthMethod === 'bearer')
                                <div class="max-w-md animate-fade-in-up bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 transition-colors">
                                    <x-api-doc::input wire:model.live="globalAuthToken" type="password" label="Bearer Token" placeholder="eyJhbG..." required="true" />
                                </div>
                            @elseif($globalAuthMethod === 'basic')
                                <div class="max-w-md animate-fade-in-up bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 transition-colors">
                                    <div class="grid grid-cols-2 gap-4">
                                        <x-api-doc::input wire:model.live="globalAuthUsername" type="text" label="Username" placeholder="admin" required="true" />
                                        <x-api-doc::input wire:model.live="globalAuthPassword" type="password" label="Password" placeholder="••••••••" required="true" />
                                    </div>
                                </div>
                            @elseif($globalAuthMethod === 'api_key')
                                <div class="max-w-xl animate-fade-in-up bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 transition-colors">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <x-api-doc::input wire:model.live="globalApiKeyName" type="text" label="Key Name" placeholder="X-API-Key" required="true" />
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                                                    <span><span class="font-mono">In</span></span>
                                                </label>
                                                <select wire:model.live="globalApiKeyLocation" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-md shadow-sm sm:text-sm outline-none focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white dark:bg-slate-800 dark:text-white transition-colors">
                                                    <option value="header">Header</option>
                                                    <option value="query">Query Params</option>
                                                </select>
                                            </div>
                                        </div>
                                        <x-api-doc::input wire:model.live="globalApiKeyValue" type="password" label="Value" placeholder="your-api-key-here" required="true" />
                                    </div>
                                </div>
                            @endif
                            
                            @if($globalAuthMethod !== 'none')
                                <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl border border-emerald-100 dark:border-emerald-500/20 flex items-start animate-fade-in-up max-w-xl transition-colors">
                                    <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="text-sm text-emerald-900 dark:text-emerald-100 font-bold">Authentication Configured</p>
                                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1 leading-relaxed">Credentials will automatically be sent when testing endpoints through the "Try it out" panel.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        /* Custom Scrollbar for better aesthetics */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #e5e7eb;
            border-radius: 20px;
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
        }
    </style>
</div>
