<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', $title ?? 'Workflow Management Hub - Empon Pawon')</title>
    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="bg-[#E2E8E8] text-[#0A1628] antialiased" x-data="dashboardSidebar()">
<x-dashboard.sidebar />

<main
    class="ml-0 flex min-h-screen flex-col transition-[margin] duration-200 ease-out"
    x-bind:class="collapsed ? 'sm:ml-[72px]' : 'sm:ml-64'"
>
    <x-dashboard.header />

    {{ $slot ?? '' }}
    @yield('content')
</main>

<x-dashboard.alert-dialog />

</body>
</html>
