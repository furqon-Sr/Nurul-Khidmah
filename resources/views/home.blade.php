<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
    <!-- AlpineJS for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    
    <!-- Navbar Component -->
    <x-frontend.navbar />

    <!-- Hero Component -->
    <x-frontend.hero />

    <!-- Programs Component -->
    <x-frontend.programs />

    <!-- Visi & Misi Component -->
    <x-frontend.visi-misi />

    <!-- News Component -->
    <x-frontend.news :news="$news" />

    <!-- Footer Component -->
    <x-frontend.footer />

</body>
</html>
