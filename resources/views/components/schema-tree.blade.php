@props(['schema', 'level' => 0])

<div class="font-mono text-[13px] leading-relaxed">
    @if(isset($schema['type']))
        <span class="text-indigo-400 font-semibold tracking-wide">{{ $schema['type'] }}</span>
    @endif
    
    @if(isset($schema['example']))
        <span class="text-slate-400 ml-2">Example: <span class="text-emerald-400/90">{{ is_array($schema['example']) ? json_encode($schema['example']) : $schema['example'] }}</span></span>
    @endif

    @if(isset($schema['properties']) && is_array($schema['properties']))
        <div class="mt-3 space-y-3">
            @foreach($schema['properties'] as $key => $property)
                <div class="flex flex-col group relative">
                    <!-- Hover spotlight effect for tree items -->
                    <div class="absolute -inset-x-2 -inset-y-1 bg-white/[0.03] opacity-0 group-hover:opacity-100 rounded transition-opacity duration-200 pointer-events-none"></div>
                    <div class="flex items-start relative z-10">
                        <!-- Connecting lines for nested levels -->
                        @if($level > 0)
                            <div class="w-4 h-full border-r border-white/10 mr-2 -mt-1 group-hover:border-white/20 transition-colors"></div>
                        @endif

                        <div class="flex-1 min-w-0 {{ $level > 0 ? 'ml-4' : '' }}">
                            <div class="flex items-baseline space-x-2">
                                <span class="font-bold text-slate-200 group-hover:text-white transition-colors">{{ $key }}</span>
                                
                                @if(isset($property['type']))
                                    <span class="text-[11px] text-indigo-400/80">{{ $property['type'] }}</span>
                                @endif

                                @if(isset($property['example']))
                                    <span class="text-[11px] text-slate-400 truncate ml-3">Example: <span class="text-emerald-400/80">{{ is_array($property['example']) ? json_encode($property['example']) : $property['example'] }}</span></span>
                                @endif
                            </div>

                            @if(isset($property['properties']) || isset($property['items']))
                                <div class="mt-1.5 pl-3 border-l-2 border-slate-800 ml-1 group-hover:border-slate-700 transition-colors">
                                    <x-api-doc::schema-tree :schema="$property" :level="$level + 1" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(isset($schema['items']) && is_array($schema['items']))
        <div class="mt-2 pl-3 border-l-2 border-slate-800 ml-1">
            <span class="text-[11px] text-slate-400 mb-1.5 block uppercase tracking-wider font-semibold">Array items:</span>
            <x-api-doc::schema-tree :schema="$schema['items']" :level="$level + 1" />
        </div>
    @endif
</div>
