<?php

include __DIR__ . '/../partials/sidebar.php';
include __DIR__ . '/../partials/header.php';

$applicant = $applicant ?? [];
$curriculumSubjects = $curriculumSubjects ?? [];
$evaluations = $evaluations ?? [];
$torUrl = $torUrl ?? '';

function e($value)
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$applicantId = (int)($applicant['applicant_id'] ?? 0);

$applicantName = trim(
    ($applicant['first_name'] ?? '') . ' ' .
    ($applicant['middle_name'] ?? '') . ' ' .
    ($applicant['surname'] ?? '')
);

$total = count($evaluations);
$approved = 0;
$pending = 0;
$rejected = 0;

foreach ($evaluations as $evaluation) {
    $status = strtolower($evaluation['status'] ?? 'pending');

    if ($status === 'approved') {
        $approved++;
    } elseif ($status === 'rejected') {
        $rejected++;
    } else {
        $pending++;
    }
}
?>

<main class="main-content">
    <div class="container-fluid">

        <!-- PAGE HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1 class="h3 fw-bold mb-1">
                    Transferee Subject Evaluation
                </h1>

                <p class="text-muted small mb-0">
                    Evaluate the applicant's previous academic records
                    against the institution's curriculum.
                </p>
            </div>

            <a href="<?= BASE_URL ?>/transferees"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                Back to Applications
            </a>

        </div>

        <!-- APPLICANT INFORMATION -->
        <div class="card shadow-sm mb-4">

            <div class="card-header bg-white">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person-vcard me-2"></i>
                    Applicant Information
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="small text-muted">
                            Applicant Name
                        </label>
                        <div class="fw-semibold">
                            <?= e($applicantName ?: 'Not available') ?>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="small text-muted">
                            Applicant ID
                        </label>
                        <div class="fw-semibold">
                            <?= $applicantId ?>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="small text-muted">
                            Intended Course
                        </label>
                        <div class="fw-semibold">
                            <?= e($applicant['course_name'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="small text-muted">
                            Previous School
                        </label>
                        <div class="fw-semibold">
                            <?= e($applicant['school_last_attended'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted">
                            Email Address
                        </label>
                        <div class="fw-semibold">
                            <?= e($applicant['email'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted">
                            Contact Number
                        </label>
                        <div class="fw-semibold">
                            <?= e($applicant['contact_number'] ?? '-') ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- EVALUATION SUMMARY -->
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">
                            Total Subjects
                        </div>
                        <h3 class="fw-bold mb-0">
                            <?= $total ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">
                            Approved Subjects
                        </div>
                        <h3 class="fw-bold text-success mb-0">
                            <?= $approved ?>
                        </h3>
                    </div>
                </div>
            </div>



            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">
                            Pending Evaluation
                        </div>
                        <h3 class="fw-bold text-warning mb-0">
                            <?= $pending ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">
                            Rejected Subjects
                        </div>
                        <h3 class="fw-bold text-danger mb-0">
                            <?= $rejected ?>
                        </h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CONTENT -->
        <div class="row g-3">

            <!-- TOR PREVIEW -->
            <div class="col-lg-5">

                <div class="card shadow-sm h-100">

                    <div class="card-header bg-white d-flex justify-content-between align-items-center">

                        <div>
                            <h6 class="fw-bold text-primary mb-0">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                Transcript of Records
                            </h6>
                            <small class="text-muted">
                                Previous school academic record
                            </small>
                        </div>

                        <?php if ($torUrl): ?>
                            <a href="<?= e($torUrl) ?>"
                               target="_blank"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Open
                            </a>
                        <?php endif; ?>

                    </div>

                    <div class="card-body p-2">

                        <?php if ($torUrl): ?>

                            <iframe
                                src="<?= e($torUrl) ?>"
                                width="100%"
                                height="650"
                                class="border rounded">
                            </iframe>

                        <?php else: ?>

                            <div class="text-center text-muted py-5">
                                <i class="bi bi-file-earmark-x fs-1"></i>
                                <p class="mt-2 mb-0">
                                    No TOR document available.
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <!-- SUBJECT EVALUATION -->
            <div class="col-lg-7">

                <div class="card shadow-sm">

                    <div class="card-header bg-white d-flex justify-content-between align-items-center">

                        <div>
                            <h6 class="fw-bold text-primary mb-0">
                                Subject Evaluation
                            </h6>
                            <small class="text-muted">
                                Match previous subjects with the curriculum.
                            </small>
                        </div>

                      <button type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#addEvaluationModal">
                        <i class="bi bi-plus-lg"></i>
                        Add Subject
                    </button>

                    </div>

                    <div class="card-body">

                        <!-- FILTERS -->
                        <div class="row g-2 mb-3">

                            <div class="col-md-4">
                                <select class="form-select form-select-sm"
                                        id="evaluationStatus">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="evaluationSearch"
                                       placeholder="Search subjects...">
                            </div>

                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive">

                            <table class="table table-hover table-striped align-middle">

                                <thead class="table-light">
                                    <tr>
                                        <th>Previous Subject</th>
                                        <th>Equivalent Subject</th>
                                        <th>Grade</th>
                                        <th>Units</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody id="evaluationTableBody">

                                    <?php if (empty($evaluations)): ?>

                                        <tr>
                                            <td colspan="6"
                                                class="text-center text-muted py-5">
                                                <i class="bi bi-clipboard2-x fs-2"></i>
                                                <p class="mb-0 mt-2">
                                                    No subjects evaluated yet.
                                                </p>
                                            </td>
                                        </tr>

                                    <?php else: ?>

                                        <?php foreach ($evaluations as $evaluation): ?>

                                            <?php
                                            $status = strtolower(
                                                $evaluation['status'] ?? 'pending'
                                            );

                                            $badge = match ($status) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                            ?>

                                            <tr data-status="<?= e($status) ?>">

                                                <td>
                                                    <div class="fw-semibold">
                                                        <?= e($evaluation['previous_subject_code'] ?? '-') ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= e($evaluation['previous_subject_name'] ?? '-') ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold">
                                                        <?= e($evaluation['subject_code'] ?? '-') ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= e($evaluation['subject_name'] ?? '-') ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <?= e($evaluation['previous_grade'] ?? '-') ?>
                                                </td>

                                                <td>
                                                    <?= e($evaluation['previous_units'] ?? '-') ?>
                                                </td>

                                                <td>
                                                    <span class="badge text-bg-<?= $badge ?>">
                                                        <?= ucfirst(e($status)) ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">

                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            onclick="viewEvaluation(<?= (int)$evaluation['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <?php if ($status === 'pending'): ?>

                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm"
                                                                onclick="approveEvaluation(<?= (int)$evaluation['id'] ?>)">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm"
                                                                onclick="rejectEvaluation(<?= (int)$evaluation['id'] ?>)">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</main>


<!-- ADD SUBJECT MODAL -->
<div class="modal fade" id="addEvaluationModal" tabindex="-1">

    <div class="modal-dialog modal-md">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    Add Previous Subject
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <form id="evaluationForm"
                      action="<?= BASE_URL ?>/transferees/evaluation/store"
                      method="POST">

                    <input type="hidden"
                           name="applicant_id"
                           value="<?= (int)$applicant['applicant_id'] ?? 0 ?>">

                    <input type="hidden"
                           name="requirement_file_id"
                           value="<?= (int)($requirement_file['requirement_file_id'] ?? 0) ?>">

                    <h6 class="text-primary fw-semibold mb-3">
                        Previous School Subject
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text"
                               class="form-control"
                               name="previous_subject_code"
                               placeholder="e.g. IT101">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text"
                               class="form-control"
                               name="previous_subject_name"
                               required>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Previous Grade</label>
                            <input type="number"
                                   class="form-control"
                                   name="previous_grade"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Previous Units</label>
                            <input type="number"
                                   class="form-control"
                                   name="previous_units"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>

                    </div>

                    <hr>

                    <h6 class="text-primary fw-semibold mb-3">
                        Equivalent Curriculum Subject
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">
                            Select Curriculum Subject
                        </label>

                        <select class="form-select"
                                name="curriculum_subject_id"
                                id="curriculum_subject_id"
                                required>

                            <option value="">
                                Select equivalent subject
                            </option>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>

                        <textarea class="form-control"
                                  name="remarks"
                                  rows="3"
                                  placeholder="Enter evaluation remarks..."></textarea>
                    </div>

                </form>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit"
                        form="evaluationForm"
                        class="btn btn-primary"
                        id="saveEvaluation">
                    Save Evaluation
                </button>

            </div>

        </div>

    </div>

</div>



<!-- VIEW EVALUATION MODAL -->
<div class="modal fade" id="viewEvaluationModal" tabindex="-1">

    <div class="modal-dialog modal-md">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Evaluation Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body"
                 id="viewEvaluationContent">
            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.CURRICULUM_ID = <?= json_encode($curriculum['curriculum_id'] ?? null) ?>;
    window.APPLICANT_ID = <?= json_encode($applicantId ?? null) ?>;
</script>

<script src="<?= BASE_URL ?>/js/transferee_evaluation.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>