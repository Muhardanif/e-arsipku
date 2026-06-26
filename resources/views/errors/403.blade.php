<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tidak Punya Akses — {{ config('app.name', 'E-ARSIPKU') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-surface font-sans text-foreground antialiased">
    <main class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-md text-center">
            <span class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-red-600">
                @svg('heroicon-o-lock-closed', 'h-8 w-8')
            </span>
            <h1 class="text-2xl font-bold text-primary">Tidak Punya Akses</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ $exception?->getMessage() ?: 'Anda tidak memiliki izin untuk membuka halaman ini.' }}
            </p>
            <x-button variant="primary" class="mt-6" :href="url('/dashboard')">
                <x-slot:icon>@svg('heroicon-o-arrow-left', 'h-5 w-5')</x-slot>
                Kembali ke Dashboard
            </x-button>
        </div>
    </main>
</body>
</html>
