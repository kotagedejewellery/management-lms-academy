<?php

namespace App\Http\Controllers;

use App\Support\DashboardResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', app(DashboardResolver::class)->for($request->user()));
    }
}
