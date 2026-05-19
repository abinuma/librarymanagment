<?php
/**
 * SettingsController - Manage application settings
 */

class SettingsController
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        try {
            $settings = $this->settingModel->getAll();
            require VIEW_PATH . '/settings/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading system settings");
            errorResponse('Unable to load settings at this time.', '/dashboard');
        }
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        try {
            $borrowDuration = (int)($_POST['borrow_duration_days'] ?? 14);
            $finePerDay = (float)($_POST['fine_per_day'] ?? 1.00);
            $maxBorrowLimit = (int)($_POST['max_borrow_limit'] ?? 5);

            if ($borrowDuration <= 0 || $finePerDay < 0 || $maxBorrowLimit <= 0) {
                errorResponse('Invalid settings provided. Please ensure all numerical values are positive.', '/settings');
                return;
            }

            $this->settingModel->update('borrow_duration_days', (string)$borrowDuration);
            $this->settingModel->update('fine_per_day', (string)$finePerDay);
            $this->settingModel->update('max_borrow_limit', (string)$maxBorrowLimit);

            successResponse('System settings updated successfully.', '/settings');
        } catch (Throwable $e) {
            logAppError($e, "Failed to update system settings");
            errorResponse('Unable to update settings right now.', '/settings');
        }
    }
}
