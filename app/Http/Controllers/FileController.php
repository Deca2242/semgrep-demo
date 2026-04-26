<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * VULN #3 — Command Injection
     * Input del usuario interpolado en un comando shell.
     * Detectado por: p/php (php.lang.security.injection.tainted-shell-command).
     * Fix: validar/whitelist el filename y usar escapeshellarg().
     */
    public function convert(Request $request)
    {
        $filename = $request->input('filename');

        $output = shell_exec("convert " . $filename . " /tmp/out.png");

        return response($output);
    }

    /**
     * VULN #4 — Path Traversal
     * Lectura de archivo basada en input del usuario sin sanitizar.
     * Detectado por: p/php (tainted-file-inclusion).
     * Fix: validar contra una lista permitida y usar basename().
     */
    public function show(Request $request)
    {
        $name = $request->input('name');

        return file_get_contents(storage_path('uploads/' . $name));
    }
}
