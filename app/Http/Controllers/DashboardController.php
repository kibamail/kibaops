<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard
     *
     * Shows the primary dashboard interface for authenticated users
     * with workspace and project overview information.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard/Dashboard');
    }
}
