
<?php
// Expected data from controller:
// $applications = [];
// $summary = [
//     'pending' => 12,
//     'for_evaluation' => 8,
//     'evaluated' => 5,
//     'credited' => 0
// ];

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="main-content">
    <div class="container">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Transferee Applications</h1>
                <p class="text-muted small mb-0">
                    Manage transferee applications and review their previous academic records.
                </p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Pending', $summary['pending'] ?? 0, 'bi-clock-history', 'warning', 'summaryPending'],
        ['For Evaluation', $summary['for_evaluation'] ?? 0, 'bi-file-earmark-text', 'primary', 'summaryForEvaluation'],
        ['Evaluated', $summary['evaluated'] ?? 0, 'bi-check-circle', 'success', 'summaryEvaluated'],
        ['Credited', $summary['credited'] ?? 0, 'bi-award', 'info', 'summaryCredited']
    ];
    ?>

    <?php foreach ($cards as [$label, $count, $icon, $color, $id]): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded bg-<?= $color ?> bg-opacity-10 p-3">
                        <i class="bi <?= $icon ?> text-<?= $color ?> fs-4"></i>
                    </div>

                    <div>
                        <p class="text-muted small mb-1">
                            <?= e($label) ?>
                        </p>

                        <h4 class="fw-bold mb-0" id="<?= $id ?>">
                            <?= (int) $count ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
        <!-- Applications Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0">Transferee Applications</h5>
                        <small class="text-muted">
                            List of students applying for transfer admission.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="statusFilter"
                                style="width: 160px;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="for_evaluation">For Evaluation</option>
                            <option value="evaluated">Evaluated</option>
                            <option value="credited">Credited</option>
                        </select>

                        <select class="form-select form-select-sm" id="courseFilter"
                                style="width: 150px;">
                            <option value="">All Courses</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSBA">BSBA</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="search" class="form-control form-control-sm"
                               id="searchApplication"
                               placeholder="Search student name or student number...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Previous School</th>
                                <th>TOR Document</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody id="applicationsTableBody">
                            <?php if (!empty($applications)): ?>
                                <?php foreach ($applications as $application): ?>
                                    <?php
                                    $status = strtolower($application['evaluation_status'] ?? 'pending');

                                    $badge = match ($status) {
                                        'pending' => 'warning',
                                        'for_evaluation' => 'primary',
                                        'evaluated' => 'success',
                                        'credited' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>

                                    <tr data-status="<?= e($status) ?>"
                                        data-course="<?= e($application['course_code'] ?? '') ?>">

                                        <td>
                                            <div class="fw-semibold">
                                                <?= e($application['student_name'] ?? '') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= e($application['student_number'] ?? 'Not yet assigned') ?>
                                            </small>
                                        </td>

                                        <td>
                                            <?= e($application['course_code'] ?? '—') ?>
                                        </td>

                                        <td>
                                            <?= e($application['previous_school'] ?? '—') ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($application['tor_file_id'])): ?>
                                                <span class="text-success small">
                                                    <i class="bi bi-file-earmark-check"></i>
                                                    TOR Uploaded
                                                </span>
                                            <?php else: ?>
                                                <span class="text-danger small">
                                                    <i class="bi bi-file-earmark-x"></i>
                                                    No TOR
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= (int)($application['subject_count'] ?? 0) ?>
                                            <small class="text-muted">subjects</small>
                                        </td>

                                        <td>
                                            <span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?>">
                                                <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/transferee/evaluation/<?= (int)$application['student_id'] ?>"
                                               class="btn btn-sm btn-primary">
                                                <?= $status === 'evaluated' || $status === 'credited'
                                                    ? 'View Evaluation'
                                                    : 'Evaluate' ?>
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No transferee applications found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/js/transferee_applications.js"></script>
