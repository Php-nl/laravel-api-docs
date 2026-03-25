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
    
    <div class="bg-black/40 rounded-lg border border-white/5 overflow-hidden">
        <pre class="p-4 overflow-x-auto text-sm font-mono text-slate-300 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent"><code>{{ is_array($response['body'] ?? null) ? json_encode($response['body'] ?? [], JSON_PRETTY_PRINT) : ($response['body'] ?? '') }}</code></pre>
    </div>
</div>
