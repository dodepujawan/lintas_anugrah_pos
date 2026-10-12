<!DOCTYPE html>
<html>
<head>
    <title>Livewire Example</title>
    <!-- Pastikan AlpineJS ada -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body>
    <div class="container" style="padding: 20px;">
        <h1>Contoh Livewire Dynamic Content</h1>

        @livewire('dynamic-content')

        <p>Ini adalah halaman utama</p>
    </div>

    @livewireScripts
</body>
</html>
