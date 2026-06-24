<?php

namespace App\Livewire\Concerns;

trait InteractsWithDashboardAlert
{
    /**
     * @param  'success'|'warning'|'danger'|'failed'  $variant
     */
    protected function dashboardAlert(string $variant, string $title, string $message): void
    {
        $allowed = ['success', 'warning', 'danger', 'failed'];
        if (! in_array($variant, $allowed, true)) {
            $variant = 'success';
        }

        $this->js('window.showDashboardAlert('.json_encode([
            'variant' => $variant,
            'title' => $title,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE).')');
    }

    protected function dashboardAlertSuccess(string $title, string $message): void
    {
        $this->dashboardAlert('success', $title, $message);
    }

    protected function dashboardAlertWarning(string $title, string $message): void
    {
        $this->dashboardAlert('warning', $title, $message);
    }

    protected function dashboardAlertDanger(string $title, string $message): void
    {
        $this->dashboardAlert('danger', $title, $message);
    }

    protected function dashboardAlertFailed(string $title, string $message): void
    {
        $this->dashboardAlert('failed', $title, $message);
    }
}
