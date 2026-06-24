<?php

namespace Tests\Feature\Concerns;

use App\Livewire\Concerns\InteractsWithDashboardAlert;
use Illuminate\View\View;
use Livewire\Component;

class DashboardAlertTraitProbe extends Component
{
    use InteractsWithDashboardAlert;

    public function fire(string $kind): void
    {
        match ($kind) {
            'success' => $this->dashboardAlertSuccess('T', 'M'),
            'warning' => $this->dashboardAlertWarning('T', 'M'),
            'danger' => $this->dashboardAlertDanger('T', 'M'),
            'failed' => $this->dashboardAlertFailed('T', 'M'),
        };
    }

    public function render(): View
    {
        return view('livewire.concerns.dashboard-alert-trait-probe');
    }
}
