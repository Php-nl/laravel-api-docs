<div class="flex h-screen bg-white font-sans text-gray-900 overflow-hidden">
    <!-- Sidebar -->
    <div class="w-72 bg-gray-50 border-r border-gray-200 flex flex-col flex-shrink-0">
        <div class="p-5 border-b border-gray-200 bg-white shadow-sm z-10">
            <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-[var(--primary-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                {{ config('laravel-api-doc.ui.title') }}
            </h1>
            <div class="mt-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Search API..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]/50 focus:border-[var(--primary-color)] bg-white transition-all shadow-sm" />
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
            @foreach($this->groups as $group => $endpoints)
                <div class="mb-6">
                    <h2 class="px-5 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                        <span class="bg-gray-200 h-px flex-1 mr-2"></span>
                        {{ $group }}
                        <span class="bg-gray-200 h-px flex-1 ml-2"></span>
                    </h2>
                    <div class="space-y-0.5 mt-3">
                        @foreach($endpoints as $endpoint)
                            <button wire:click="selectEndpoint('{{ $endpoint['id'] }}')" class="w-full text-left px-5 py-2.5 hover:bg-gray-100/80 transition-all group flex items-start space-x-3 border-l-2 {{ $selectedId === $endpoint['id'] ? 'bg-blue-50/50 border-[var(--primary-color)]' : 'border-transparent' }}">
                                <div class="mt-0.5 w-14 flex-shrink-0">
                                    <x-api-doc::badge :method="$endpoint['methods'][0]" class="text-[10px] px-1.5 py-0.5 rounded shadow-sm opacity-90 group-hover:opacity-100 {{ $selectedId === $endpoint['id'] ? 'opacity-100 ring-1 ring-offset-1 ring-[var(--primary-color)]/20' : '' }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate {{ $selectedId === $endpoint['id'] ? 'text-[var(--primary-color)]' : 'text-gray-700 group-hover:text-gray-900' }}">
                                        {{ $endpoint['name'] ?: $endpoint['uri'] }}
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
        </div>
    </div>

    <!-- Main Content Split (Middle and Right) -->
    <div class="flex-1 flex overflow-hidden bg-white">
        @if($this->selectedEndpoint)
            <!-- Middle Column: Details -->
            <div class="flex-1 overflow-y-auto w-[40%] min-w-[500px]">
                <div class="p-10 lg:p-14 max-w-3xl xl:max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $this->selectedEndpoint['name'] ?? $this->selectedEndpoint['uri'] }}</h2>
                        
                        <div class="mt-6 flex items-center space-x-4">
                            @foreach($this->selectedEndpoint['methods'] as $method)
                                <x-api-doc::badge :method="$method" class="px-3 py-1.5 text-sm font-semibold shadow-sm rounded-md" />
                            @endforeach
                            <div class="flex-1 flex items-center bg-gray-50 rounded-md px-4 py-2.5 border border-gray-200 shadow-sm">
                                <span class="text-gray-400 mr-1 select-none font-mono text-sm">{{ rtrim(url('/'), '/') }}</span>
                                <span class="text-gray-900 font-mono text-sm font-semibold">{{ str_starts_with($this->selectedEndpoint['uri'], '/') ? $this->selectedEndpoint['uri'] : '/' . $this->selectedEndpoint['uri'] }}</span>
                            </div>
                        </div>
                    </div>

                    @if($this->selectedEndpoint['description'])
                        <div class="prose prose-slate max-w-none text-gray-600 mb-12 border-l-4 border-gray-200 pl-4 py-1">
                            {{ $this->selectedEndpoint['description'] }}
                        </div>
                    @endif

                    @if(!empty($this->selectedEndpoint['parameters']))
                        <div class="mt-12">
                            <h3 class="text-xl font-semibold text-gray-900 mb-5 pb-2 border-b border-gray-200 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Request Parameters
                            </h3>
                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-5 py-3.5 font-semibold text-gray-700 w-1/3">Name</th>
                                            <th class="px-5 py-3.5 font-semibold text-gray-700 w-1/4">Type</th>
                                            <th class="px-5 py-3.5 font-semibold text-gray-700">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach($this->selectedEndpoint['parameters'] as $parameter)
                                        <tr class="hover:bg-gray-50/50 transition-colors group">
                                            <td class="px-5 py-4 align-top">
                                                <div class="font-mono text-gray-900 font-semibold flex items-center">
                                                    {{ $parameter['name'] }}
                                                    @if($parameter['required'])
                                                        <span class="text-red-500 ml-1.5 text-xs bg-red-50 px-1.5 py-0.5 rounded border border-red-100" title="Required">required</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-emerald-600 font-mono text-xs align-top pt-4.5">{{ $parameter['type'] }}</td>
                                            <td class="px-5 py-4 text-gray-600 align-top">{{ $parameter['description'] ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Try It Out -->
            <div class="w-[45%] lg:w-[480px] xl:w-[500px] flex-shrink-0 bg-[#0f111a] overflow-y-auto border-l border-slate-800 flex flex-col shadow-2xl z-20">
                <div class="p-8">
                    <h3 class="text-lg font-semibold text-white mb-6 flex items-center justify-between pb-3 border-b border-white/10">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[var(--primary-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Try it out
                        </span>
                    </h3>

                    <div class="space-y-6">
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
                                            wire:model.live="tryItOutForm.{{ $parameter['name'] }}" 
                                            :label="$parameter['name']" 
                                            :required="$parameter['required']" 
                                            :description="$parameter['description']" 
                                            type-hint="{{ $parameter['type'] }}"
                                            theme="dark"
                                        />
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
                        <p class="text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">Welcome to the interactive API documentation. Explore all available endpoints, required parameters, and test API requests live from this dashboard.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </div>
                            <span class="text-3xl font-bold text-gray-900 mb-1">{{ count($this->endpoints) }}</span>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Endpoints</span>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1" style="transition-delay: 50ms;">
                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <span class="text-3xl font-bold text-gray-900 mb-1">{{ count($this->groups) }}</span>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Groups</span>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center transition-all hover:shadow-md hover:-translate-y-1" style="transition-delay: 100ms;">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            </div>
                            <span class="text-xl font-bold text-gray-900 mb-1 break-all truncate w-full">{{ parse_url(url('/'), PHP_URL_HOST) ?? 'Local' }}</span>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest mt-1">Environment</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-8 lg:p-10 shadow-sm border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-2 h-full bg-[var(--primary-color)]"></div>
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            Getting Started
                        </h3>
                        <div class="space-y-5 text-gray-600">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-1">Explore Navigation</h4>
                                    <p class="text-sm leading-relaxed">Navigate using the sidebar on the left to explore available endpoints. The API is organized in logical groups based on domains.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-1">Authenticate Requests</h4>
                                    <p class="text-sm leading-relaxed">If an endpoint requires authentication, you can append your <code class="bg-gray-100 text-[var(--primary-color)] px-1.5 py-0.5 rounded font-mono text-xs border border-gray-200">Bearer Token</code> using the input inside the <strong>"Try it out"</strong> panel on the right side.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center mr-4 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-1">Live Testing</h4>
                                    <p class="text-sm leading-relaxed">Most endpoints can be executed straight from this dashboard to check responses live in your current environment by filling in required parameters and clicking "Send Request".</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Configuration -->
                    <div class="bg-white rounded-3xl p-8 lg:p-10 shadow-sm border border-gray-100 mt-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-2 h-full bg-slate-800"></div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Security & Authentication
                        </h3>
                        <p class="text-sm text-gray-500 mb-8 pl-8">Configure global authentication credentials to automatically apply them to requests in the "Try it out" panel.</p>

                        <div class="pl-8">
                            <div class="flex flex-wrap items-center gap-6 mb-8">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="none" class="w-4 h-4 text-[var(--primary-color)] border-gray-300 focus:ring-[var(--primary-color)] cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-gray-900 transition-colors">None</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" wire:model.live="globalAuthMethod" value="bearer" class="w-4 h-4 text-[var(--primary-color)] border-gray-300 focus:ring-[var(--primary-color)] cursor-pointer">
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
                                <div class="max-w-md animate-fade-in-up bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <x-api-doc::input wire:model.live="globalAuthToken" type="password" label="Bearer Token" placeholder="eyJhbG..." required="true" />
                                </div>
                            @elseif($globalAuthMethod === 'basic')
                                <div class="max-w-md animate-fade-in-up bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <div class="grid grid-cols-2 gap-4">
                                        <x-api-doc::input wire:model.live="globalAuthUsername" type="text" label="Username" placeholder="admin" required="true" />
                                        <x-api-doc::input wire:model.live="globalAuthPassword" type="password" label="Password" placeholder="••••••••" required="true" />
                                    </div>
                                </div>
                            @elseif($globalAuthMethod === 'api_key')
                                <div class="max-w-xl animate-fade-in-up bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <x-api-doc::input wire:model.live="globalApiKeyName" type="text" label="Key Name" placeholder="X-API-Key" required="true" />
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                                                    <span><span class="font-mono">In</span></span>
                                                </label>
                                                <select wire:model.live="globalApiKeyLocation" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm outline-none focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white transition-colors">
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
                                <div class="mt-6 p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex items-start animate-fade-in-up max-w-xl">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="text-sm text-emerald-900 font-bold">Authentication Configured</p>
                                        <p class="text-xs text-emerald-700 mt-1 leading-relaxed">Credentials will automatically be sent when testing endpoints through the "Try it out" panel.</p>
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
