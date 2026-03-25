<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('laravel-api-doc.ui.title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        :root {
            --primary-color: {{ config('laravel-api-doc.ui.theme.primary_color') }};
            --background-color: {{ config('laravel-api-doc.ui.theme.background_color') }};
        }
    </style>
    @livewireStyles
</head>
<body class="bg-[var(--background-color)]">
    {{ $slot }}
    @livewireScripts
</body>
</html>
