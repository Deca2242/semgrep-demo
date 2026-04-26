# Semgrep Demo — Laravel + GitHub Actions

Proyecto de ejemplo para mostrar **SAST con Semgrep** integrado a un pipeline de **GitHub Actions** siguiendo **Gitflow**, basado en el material del PPT *"CI con Semgrep"*.

El código tiene **7 vulnerabilidades sembradas a propósito** para que el escaneo siempre tenga qué reportar.

---

## Estructura

```
semgrep-demo/
├── .github/workflows/semgrep.yml         ← Pipeline (Gitflow + baseline + SARIF)
├── .semgrep/no-hardcoded-credentials.yml ← Regla custom propia
├── app/
│   ├── Http/Controllers/
│   │   ├── UserController.php   (SQLi, mass assignment)
│   │   └── FileController.php   (command injection, path traversal)
│   ├── Services/PaymentService.php  (Stripe key + Bearer hardcoded)
│   └── Helpers/Crypto.php       (MD5 para passwords)
├── resources/views/search.blade.php  (XSS por {!! !!})
├── routes/web.php
├── composer.json
└── .env.example
```

---

## Vulnerabilidades sembradas

| # | Archivo | Vuln | Detectado por |
|---|---------|------|---------------|
| 1 | `UserController::search` | SQL Injection | `p/php`, `p/laravel` |
| 2 | `UserController::store`  | Mass assignment | `p/laravel` |
| 3 | `FileController::convert` | Command injection (`shell_exec`) | `p/php` |
| 4 | `FileController::show`   | Path traversal | `p/php` |
| 5 | `PaymentService`         | Stripe key + Bearer hardcoded | `p/secrets` + **regla custom** |
| 6 | `Crypto::hashPassword`   | MD5 para passwords (weak crypto) | `p/php` |
| 7 | `search.blade.php`       | XSS por `{!! $query !!}` | `p/laravel` |

---

## Cómo correrlo localmente (sin GitHub Actions)

Con Docker, sin instalar nada en el host:

```bash
cd semgrep-demo

docker run --rm -v "$PWD:/src" -w /src returntocorp/semgrep \
  semgrep scan \
    --config=p/ci \
    --config=p/php \
    --config=p/laravel \
    --config=p/secrets \
    --config=.semgrep/ \
    ./app ./resources ./routes
```

Deberías ver los 7 findings (algunos pueden duplicarse entre packs).

Para generar el reporte SARIF que sube el pipeline:

```bash
docker run --rm -v "$PWD:/src" -w /src returntocorp/semgrep \
  semgrep scan --config=p/ci --config=p/php --config=p/laravel \
               --config=p/secrets --config=.semgrep/ \
               --sarif --output=results.sarif \
               ./app ./resources ./routes
```

---

## Cómo funciona el pipeline (Gitflow)

`.github/workflows/semgrep.yml` aplica la matriz del slide 5 del PPT:

| Evento | Política |
|--------|----------|
| Push a `feature/*` | (no dispara — escaneo barato es trabajo del dev) |
| PR a `develop` | **Baseline scan** — solo reporta findings *nuevos* respecto a `develop`. No bloquea. |
| Push a `develop` | Full scan, no bloquea (visible en Security tab). |
| PR a `main` | **Estricto** — falla el build si hay severidad `ERROR`. |
| Push a `main`, `release/*`, `hotfix/*` | **Estricto** — `--error --severity=ERROR`. |

Los flags estrictos y de baseline se calculan en el step **"Determinar política según rama"** y se inyectan al `semgrep scan`.

El SARIF se publica con `github/codeql-action/upload-sarif@v3` → aparece en la pestaña **Security → Code scanning** del repo.

---

## Cómo probarlo en un repo real

1. Crear un repo nuevo en GitHub.
2. Copiar el contenido de esta carpeta a la raíz del repo.
3. Crear las ramas `develop` y `main` (Gitflow base).
4. **Branch protection** en `main`: marcar *Require status checks* → seleccionar `Semgrep scan`.
5. Hacer un PR de `feature/test` → `develop` modificando un archivo: el job corre con baseline, no rompe.
6. Hacer un PR de `develop` → `main`: el job corre estricto y bloquea por las 7 vulns hasta que se arreglen.

---

## Cómo apagar las vulnerabilidades

Cuando quieras "verlo verde", arregla cada archivo según el comentario `Fix:` que tiene en su docblock. El más rápido para probar es `Crypto.php`: cambiar `md5($password)` por `password_hash($password, PASSWORD_BCRYPT)` y volver a correr el escaneo.

---

## Próximos pasos sugeridos

- Conectar el repo a [semgrep.dev](https://semgrep.dev) (AppSec Platform) para que comente directamente en cada PR (slide 14, opción A).
- Agregar más reglas custom en `.semgrep/` para convenciones internas (ej. prohibir `dd()`, `dump()` o `env()` fuera de config).
- Replicar el mismo workflow en GitLab CI o Azure Pipelines (slides 9 y 10) — la lógica de Gitflow es idéntica, sólo cambia el YAML.
