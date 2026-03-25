@props(['endpoint', 'form' => []])

@php
    $smMethod = $endpoint['methods'][0] ?? 'GET';
    $smUri = str_starts_with($endpoint['uri'], '/') ? $endpoint['uri'] : '/' . $endpoint['uri'];
    
    // Replace path variables with form data or defaults
    $pathParams = $form['path'] ?? [];
    foreach ($pathParams as $key => $value) {
        if ($value !== '') {
            $smUri = str_replace('{' . $key . '}', (string) $value, $smUri);
        }
    }

    $smUrl = rtrim(url('/'), '/') . $smUri;
    
    // Build query string
    $queryParams = $form['query'] ?? [];
    $queryParams = array_filter($queryParams, fn($val) => $val !== '');
    if (!empty($queryParams)) {
        $smUrl .= '?' . http_build_query($queryParams);
    }

    // Build body JSON
    $bodyParams = $form['body'] ?? [];
    $bodyParams = array_filter($bodyParams, fn($val) => $val !== ''); // basic filter
    $bodyJson = !empty($bodyParams) ? json_encode($bodyParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';
@endphp

<div class="flex space-x-2">
    <button @click="snippetLang = 'curl'" :class="snippetLang === 'curl' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">cURL</button>
    <button @click="snippetLang = 'javascript'" :class="snippetLang === 'javascript' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">JavaScript</button>
    <button @click="snippetLang = 'php'" :class="snippetLang === 'php' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">PHP</button>
    <button @click="snippetLang = 'python'" :class="snippetLang === 'python' ? 'bg-[#1e293b] text-white border-slate-700' : 'bg-transparent text-slate-500 border-transparent hover:text-slate-300'" class="px-3 py-1.5 text-xs font-semibold rounded-md border transition-colors">Python</button>
</div>

<div class="bg-[#0b0f19] rounded-xl border border-slate-800 p-4 font-mono text-[13px] text-slate-300 overflow-x-auto shadow-inner relative group/copy mt-4">
    <button x-on:click="navigator.clipboard.writeText($refs[snippetLang].innerText)" class="absolute top-3 right-3 p-1.5 bg-slate-800 text-slate-400 hover:text-white rounded opacity-0 group-hover/copy:opacity-100 transition-opacity" title="Copy to clipboard">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
    </button>
    
    <!-- cURL -->
    <div x-show="snippetLang === 'curl'" x-ref="curl">
<pre>curl --request {{ $smMethod }} \
  --url "{{ $smUrl }}" \
  --header 'Accept: application/json'@if($bodyJson !== '') \
  --header 'Content-Type: application/json' \
  --data '{!! addslashes($bodyJson) !!}'@endif</pre>
    </div>
    
    <!-- JavaScript -->
    <div x-show="snippetLang === 'javascript'" x-ref="javascript" x-cloak>
<pre>fetch('{{ $smUrl }}', {
  method: '{{ $smMethod }}',
  headers: {
    'Accept': 'application/json'@if($bodyJson !== ''),
    'Content-Type': 'application/json'@endif

  }@if($bodyJson !== ''),
  body: JSON.stringify({!! $bodyJson !!})@endif

})
  .then(response => response.json())
  .then(response => console.log(response))
  .catch(err => console.error(err));</pre>
    </div>
    
    <!-- PHP -->
    <div x-show="snippetLang === 'php'" x-ref="php" x-cloak>
<pre>$response = Http::withHeaders([
    'Accept' => 'application/json',
])@if($bodyJson !== '')->withBody('{!! addslashes($bodyJson) !!}', 'application/json')@endif->{{ strtolower($smMethod) }}('{{ $smUrl }}');

return $response->json();</pre>
    </div>

    <!-- Python -->
    <div x-show="snippetLang === 'python'" x-ref="python" x-cloak>
<pre>import requests
@if($bodyJson !== '')import json
@endif

url = "{{ $smUrl }}"
headers = {
    "Accept": "application/json"@if($bodyJson !== ''),
    "Content-Type": "application/json"@endif

}
@if($bodyJson !== '')
payload = {!! $bodyJson !!}
@endif

response = requests.request(
    "{{ $smMethod }}", 
    url, 
    headers=headers @if($bodyJson !== ''), 
    json=payload @endif

)

print(response.json())</pre>
    </div>
</div>
