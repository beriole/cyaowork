<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'CyaoWork — Trouvez la bonne main-d\'œuvre, en toute confiance')</title>
    <meta name="description" content="CyaoWork connecte employeurs et travailleurs vérifiés (main-d'œuvre & personnel domestique) au Cameroun." />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body class="antialiased">
    @yield('body')

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();

            /* scroll reveal */
            const io = new IntersectionObserver(es => es.forEach(e => {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            }), { threshold: .12 });
            document.querySelectorAll('.reveal').forEach(el => io.observe(el));

            /* animated counters */
            const fmt = n => n.toLocaleString('fr-FR');
            const cIO = new IntersectionObserver(es => es.forEach(e => {
                if (!e.isIntersecting) return; const el = e.target; cIO.unobserve(el);
                const end = +el.dataset.count, div = +(el.dataset.divide || 1), suf = el.dataset.suffix || '';
                let cur = 0; const step = Math.max(1, Math.floor(end / 60));
                const t = setInterval(() => { cur += step; if (cur >= end) { cur = end; clearInterval(t); }
                    el.textContent = (div > 1 ? (cur / div).toFixed(1).replace('.', ',') : fmt(cur)) + suf; }, 16);
            }), { threshold: .6 });
            document.querySelectorAll('[data-count]').forEach(el => cIO.observe(el));
        });
    </script>
    @stack('scripts')
</body>
</html>
