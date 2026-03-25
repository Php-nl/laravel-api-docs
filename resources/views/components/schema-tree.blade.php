@props(['schema', 'level' => 0])

<div class="font-mono text-sm">
    @if(isset($schema['type']))
        <span class="text-purple-400">{{ $schema['type'] }}</span>
    @endif
    
    @if(isset($schema['example']))
        <span class="text-gray-500 ml-2">Example: <span class="text-green-400">{{ is_array($schema['example']) ? json_encode($schema['example']) : $schema['example'] }}</span></span>
    @endif

    @if(isset($schema['properties']) && is_array($schema['properties']))
        <div class="mt-2 space-y-2">
            @foreach($schema['properties'] as $key => $property)
                <div class="flex flex-col">
                    <div class="flex items-start">
                        <!-- Connecting lines for nested levels -->
                        @if($level > 0)
                            <div class="w-4 h-full border-r border-white/10 mr-2 -mt-1"></div>
                        @endif

                        <div class="flex-1 min-w-0 {{ $level > 0 ? 'ml-4' : '' }}">
                            <div class="flex items-baseline space-x-2">
                                <span class="font-semibold text-gray-200">{{ $key }}</span>
                                
                                @if(isset($property['type']))
                                    <span class="text-xs text-purple-400/80">{{ $property['type'] }}</span>
                                @endif

                                @if(isset($property['example']))
                                    <span class="text-xs text-gray-500 truncate">Example: <span class="text-green-400/80">{{ is_array($property['example']) ? json_encode($property['example']) : $property['example'] }}</span></span>
                                @endif
                            </div>

                            @if(isset($property['properties']) || isset($property['items']))
                                <div class="mt-1 pl-2 border-l border-white/10 ml-1">
                                    <x-api-doc::schema-tree :schema="$property" :level="$level + 1" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(isset($schema['items']) && is_array($schema['items']))
        <div class="mt-1 pl-2 border-l border-white/10 ml-1">
            <span class="text-xs text-gray-500 mb-1 block">Array items:</span>
            <x-api-doc::schema-tree :schema="$schema['items']" :level="$level + 1" />
        </div>
    @endif
</div>
