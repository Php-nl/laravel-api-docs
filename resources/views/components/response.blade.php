@props(['response'])

<div class="mt-8 animate-fade-in">
    <div class="flex items-center justify-between mb-3 border-b border-white/10 pb-2">
        <h4 class="text-sm font-semibold text-gray-200">Response</h4>
        <div class="flex items-center space-x-4 text-xs">
            <span class="font-mono flex items-center space-x-1 {{ ($response['status'] ?? 500) >= 200 && ($response['status'] ?? 500) < 300 ? 'text-green-400' : 'text-red-400' }}">
                <div class="w-2 h-2 rounded-full {{ ($response['status'] ?? 500) >= 200 && ($response['status'] ?? 500) < 300 ? 'bg-green-400' : 'bg-red-400' }}"></div>
                <span>{{ $response['status'] ?? 500 }}</span>
            </span>
            <span class="text-slate-400 flex items-center space-x-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $response['duration'] ?? 0 }}ms</span>
            </span>
        </div>
    </div>
    
    <div class="bg-zinc-950/80 backdrop-blur-md rounded-xl border border-white/5 overflow-hidden shadow-[0_0_15px_-3px_rgba(0,0,0,0.5)]" x-data="{ responseTab: 'body' }">
        <div class="flex items-center px-2 pt-1 border-b border-white/5 bg-black/50 space-x-1">
            <button @click="responseTab = 'body'" :class="responseTab === 'body' ? 'text-white border-b-2 border-[var(--primary-color)]' : 'text-slate-400 hover:text-slate-200'" class="px-4 py-2.5 text-[11px] font-bold tracking-widest uppercase transition-colors focus:outline-none -mb-px">Body</button>
            <button @click="responseTab = 'headers'" :class="responseTab === 'headers' ? 'text-white border-b-2 border-[var(--primary-color)]' : 'text-slate-400 hover:text-slate-200'" class="px-4 py-2.5 text-[11px] font-bold tracking-widest uppercase transition-colors focus:outline-none -mb-px">Headers</button>
        </div>
        
        <div class="bg-black/60 min-h-[100px] shadow-inner">
            <!-- Body Tab -->
            <div x-show="responseTab === 'body'" class="w-full">
                @php $responseBodyStr = is_array($response['body'] ?? null) ? json_encode($response['body'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($response['body'] ?? ''); @endphp
                <pre wire:key="resp-body-{{ md5($responseBodyStr) }}" class="p-4 overflow-x-auto text-[13px] font-mono text-slate-300 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent"><code class="language-json" x-init="hljs.highlightElement($el)">{{ $responseBodyStr }}</code></pre>
            </div>
            
            <!-- Headers Tab -->
            <div x-show="responseTab === 'headers'" x-cloak class="w-full p-5 overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px] font-mono">
                    <tbody class="divide-y divide-slate-800/50">
                        @foreach($response['headers'] ?? [] as $key => $values)
                            <tr>
                                <td class="py-2.5 pr-6 text-indigo-400 font-semibold align-top whitespace-nowrap">{{ $key }}</td>
                                <td class="py-2.5 text-slate-300 break-all">{{ is_array($values) ? implode(', ', $values) : $values }}</td>
                            </tr>
                        @endforeach
                        @if(empty($response['headers']))
                            <tr>
                                <td class="py-3 text-slate-500 italic text-center">No headers returned.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
