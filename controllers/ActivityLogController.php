<?php
// ============================================================
// controllers/ActivityLogController.php  –  Admin audit trail
// ============================================================

class ActivityLogController
{
    private ActivityLogModel $model;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->model = new ActivityLogModel();
    }

    public function index(): void
    {
        $filters = [
            'search'     => trim($_GET['search'] ?? ''),
            'action'     => $_GET['action']     ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date'   => $_GET['end_date']   ?? '',
        ];

        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $result = $this->model->getFiltered($filters, PER_PAGE, ($page - 1) * PER_PAGE);
        $pager  = new Paginator($result['total'], PER_PAGE, $page);

        $logs    = $result['rows'];
        $actions = $this->model->distinctActions();

        require BASE_PATH . '/views/logs/index.php';
    }
}
