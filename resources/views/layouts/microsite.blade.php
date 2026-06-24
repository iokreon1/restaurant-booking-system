
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', $title ?? 'Microsite')</title>
    @vite(['resources/css/microsite.css', 'resources/js/microsite.js'])
    @stack('head')
    @livewireStyles
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-surface-dim font-body text-on-surface antialiased">

    <main class="pt-14 pb-28 w-full max-w-[480px] min-h-dvh mx-auto bg-surface relative overflow-x-hidden ">



        {{ $slot ?? '' }}
        @yield('content')
    </main>
    <x-microsite.bottom-nav :active="$activeNav ?? 'menu'" />

    @livewireScripts
</body>

</html>
