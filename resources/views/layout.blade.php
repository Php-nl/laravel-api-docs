<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('laravel-api-doc.ui.title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/tokyo-night-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        primary: 'var(--primary-color)',
                        background: 'var(--background-color)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-color: {{ config('laravel-api-doc.ui.theme.primary_color') }};
            --background-color: {{ config('laravel-api-doc.ui.theme.background_color') }};
            --text-color: #0f172a;
        }
        .dark {
            --background-color: #09090b; /* Zinc 950 */
            --text-color: #f8fafc;
        }
        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }
        /* Code Ligatures & Highlight Adjustments */
        code, pre { font-variant-ligatures: contextual; }
        .hljs { background: transparent !important; padding: 0 !important; }
    </style>
    @livewireStyles
</head>
<body class="bg-[var(--background-color)]">
    {{ $slot }}
    @livewireScripts
</body>
</html>
