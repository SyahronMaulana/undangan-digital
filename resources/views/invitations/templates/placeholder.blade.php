<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Undangan Pernikahan {{ $wedding->coupleNames() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-stone-100 text-stone-800">
<main class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-6 text-center">
    @if ($isPreview)<p class="mb-4 rounded bg-amber-100 px-3 py-1 text-xs text-amber-900">Pratinjau draf — belum dipublikasikan</p>@endif
    <p class="text-sm">Undangan Pernikahan</p>
    <h1 class="mt-3 text-4xl font-semibold">{{ $wedding->coupleNames() }}</h1>
    @if ($wedding->event_date)<p class="mt-3">{{ $wedding->event_date->translatedFormat('d F Y') }}</p>@endif
    <p class="mt-8 text-sm">Kepada Yth.</p>
    <p class="text-xl font-medium">{{ $recipientName }}</p>
    <p class="mt-10 text-xs text-stone-500">Desain {{ $wedding->template->name }} dibuat pada Tahap 4.</p>
</main>
</body>
</html>
