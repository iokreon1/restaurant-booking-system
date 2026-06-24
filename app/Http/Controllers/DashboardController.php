<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = request()->user();

        return view('app.dashboard.index', [
            ...$this->dashboardService->getDashboardData(),
            'user' => $user,
        ]);
    }
}
