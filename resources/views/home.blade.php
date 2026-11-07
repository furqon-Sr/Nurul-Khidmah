<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    @include('partials.fonts')    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Manrope', sans-serif; }

        /* Smooth scroll: tautan anchor & tombol "kembali ke atas" bergulir halus, tidak tertutup navbar sticky */
        html { scroll-padding-top: 5rem; }

        /* Animasi muncul saat di-scroll. Hanya aktif bila JS berjalan (kelas js-reveal) agar tanpa JS konten tetap tampil. */
        .js-reveal [data-reveal] {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .7s cubic-bezier(.22, 1, .36, 1), transform .7s cubic-bezier(.22, 1, .36, 1);
            transition-delay: var(--reveal-delay, 0ms);
            will-change: opacity, transform;
        }
        .js-reveal [data-reveal].is-visible {
            opacity: 1;
            transform: none;
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto !important; }
            .js-reveal [data-reveal] { opacity: 1; transform: none; transition: none; }
        }
    </style>
    <!-- AlpineJS for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    
    <!-- Navbar Component -->
    <x-frontend.navbar />

    <!-- Hero Component -->
    <x-frontend.hero />

    <div id="reveal-scope">
        <!-- Programs Component -->
        <x-frontend.programs />

        <!-- Visi & Misi Component -->
        <x-frontend.visi-misi />

        <!-- News Component -->
        <x-frontend.news :news="$news" />

        <!-- Footer Component -->
        <x-frontend.footer />
    </div>

    <script>
        // Animasi muncul halus saat elemen masuk viewport (menghormati prefers-reduced-motion)
        (function () {
            var scope = document.getElementById('reveal-scope');
            if (!scope || !('IntersectionObserver' in window)) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            var targets = scope.querySelectorAll('section h2, section .grid > *, footer .grid > *, footer > div > div:last-child');
            targets.forEach(function (el) {
                var index = Array.prototype.indexOf.call(el.parentElement.children, el);
                el.setAttribute('data-reveal', '');
                el.style.setProperty('--reveal-delay', Math.min(index, 4) * 90 + 'ms');
            });
            document.documentElement.classList.add('js-reveal');

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

            targets.forEach(function (el) { observer.observe(el); });
        })();
    </script>

</body>
</html>
