<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * VULN #1 — SQL Injection
     * Concatenación de input del usuario en una query cruda.
     * Detectado por: p/php · p/laravel (regla tainted-sql-string).
     * Fix: usar bindings -> DB::select('SELECT * FROM users WHERE name = ?', [$name])
     */
    public function search(Request $request)
    {
        $name = $request->input('name');

        $users = DB::select("SELECT * FROM users WHERE name = '" . $name . "'");

        return response()->json($users);
    }

    /**
     * VULN #2 — Mass assignment
     * Pasar todo el request->all() al modelo sin filtrar.
     * Detectado por: p/laravel (laravel-mass-assignment).
     * Fix: usar $request->only([...]) o un FormRequest con validated().
     */
    public function store(Request $request)
    {
        return \App\Models\User::create($request->all());
    }
}
