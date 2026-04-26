<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de búsqueda</title>
</head>
<body>
    <h1>Resultados</h1>

    {{-- VULN #7 — XSS en Blade
         {!! ... !!} renderiza HTML sin escapar.
         Detectado por: p/laravel (laravel-blade-unescaped).
         Fix: usar {{ $query }} (escape automático con e()/htmlspecialchars). --}}
    <p>Buscaste: {!! $query !!}</p>

    <ul>
        @foreach ($results as $r)
            <li>{{ $r->title }}</li>
        @endforeach
    </ul>
</body>
</html>
