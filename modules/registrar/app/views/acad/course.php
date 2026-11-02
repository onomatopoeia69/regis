<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Transferee Evaluation</h1>
            <p class="text-muted small">
                Evaluate transferee academic records and credit equivalent curriculum subjects.
            </p>
        </div>

    </div>


    <!-- Transferees List -->
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h3 class="fs-6 mb-0 text-primary">
                Transferees List
            </h3>

            <div class="d-flex gap-2">

                <button type="button" class="btn btn-outline-danger btn-sm" id="pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </button>

                <button type="button" class="btn btn-outline-success btn-sm" id="excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </button>

                <button type="button" class="btn btn-outline-primary btn-sm" id="csv">
                    <i class="bi bi-filetype-csv"></i> CSV
                </button>

            </div>

        </div>


        <div class="card-body">

            <!-- Table Filters -->
            <div class="row g-2 mb-3">

                <div class="col-md-2">
                    <label class="form-label small">Per Page</label>

                    <select class="form-select form-select-sm" id="limit">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="40">40</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Order</label>

                    <select class="form-select form-select-sm" id="order">
                        <option value="desc">Descending (Newest)</option>
                        <option value="asc">Ascending (Oldest)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Evaluation Status</label>

                    <select class="form-select form-select-sm" id="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small">Search</label>

                    <input type="text"
                           class="form-control form-control-sm"
                           placeholder="Search student..."
                           id="search">
                </div>

            </div>


            <!-- Table -->
            <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Student No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>TOR</th>
                            <th>Evaluation</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="transfereesTableBody">
                    </tbody>

                </table>

            </div>


            <!-- Pagination -->
            <div class="d-flex align-items-center justify-content-between mt-3">

                <div class="fw-semibold small" id="pageInfo"></div>

                <div id="pagination" class="d-flex gap-1"></div>

            </div>

        </div>

    </div>

</div>
</main>


<!-- TRANSFEREE EVALUATION MODAL -->

<div class="modal fade"
     id="evaluationModal"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">

                <div>
                    <h5 class="modal-title fw-semibold">
                        Transferee Academic Evaluation
                    </h5>

                    <div class="small text-muted" id="evaluationStudentName">
                        Student Information
                    </div>
                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        id="closeEvaluationBtn">
                </button>

            </div>


            <!-- Body -->
            <div class="modal-body">

                <div class="row g-3">

                    <!-- STUDENT INFORMATION -->
                    <div class="col-12">

                        <div class="border rounded p-3 bg-light">

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <div class="small text-muted">Student Number</div>
                                    <div class="fw-semibold" id="student_number">-</div>
                                </div>

                                <div class="col-md-3">
                                    <div class="small text-muted">Student Name</div>
                                    <div class="fw-semibold" id="student_name">-</div>
                                </div>

                                <div class="col-md-3">
                                    <div class="small text-muted">Course</div>
                                    <div class="fw-semibold" id="student_course">-</div>
                                </div>

                                <div class="col-md-3">
                                    <div class="small text-muted">Year Level</div>
                                    <div class="fw-semibold" id="student_year">-</div>
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- TOR PREVIEW -->
                    <div class="col-lg-5">

                        <div class="border rounded h-100">

                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">

                                <div>
                                    <h6 class="fw-semibold mb-0">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                        Transcript of Records
                                    </h6>

                                    <small class="text-muted">
                                        Previous School Document
                                    </small>
                                </div>

                                <a href="#"
                                   target="_blank"
                                   class="btn btn-outline-primary btn-sm"
                                   id="openTor">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Open
                                </a>

                            </div>

                            <div class="p-2">

                                <iframe
                                    id="torPreview"
                                    src=""
                                    width="100%"
                                    height="600"
                                    class="border rounded">
                                </iframe>

                            </div>

                        </div>

                    </div>


                    <!-- SUBJECT EVALUATION -->
                    <div class="col-lg-7">

                        <div class="border rounded">

                            <div class="p-3 border-bottom">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div>
                                        <h6 class="fw-semibold mb-0">
                                            Subject Evaluation
                                        </h6>

                                        <small class="text-muted">
                                            Match previous subjects with your curriculum.
                                        </small>
                                    </div>

                                    <button type="button"
                                            class="btn btn-primary btn-sm"
                                            id="addEvaluation">
                                        <i class="bi bi-plus-lg"></i>
                                        Add Subject
                                    </button>

                                </div>

                            </div>


                            <!-- Evaluation Table -->
                            <div class="table-responsive">

                                <table class="table table-hover table-striped align-middle mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th>Previous Subject</th>
                                            <th>Equivalent Subject</th>
                                            <th>Grade</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="evaluationTableBody">

                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No subjects evaluated yet.
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- EVALUATION SUMMARY -->
                        <div class="border rounded p-3 mt-3">

                            <h6 class="fw-semibold mb-3">
                                Evaluation Summary
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <div class="small text-muted">Total Evaluated</div>
                                    <h5 class="fw-bold mb-0" id="totalEvaluated">0</h5>
                                </div>

                                <div class="col-md-4">
                                    <div class="small text-muted">Approved / Credited</div>
                                    <h5 class="fw-bold text-success mb-0" id="totalApproved">0</h5>
                                </div>

                                <div class="col-md-4">
                                    <div class="small text-muted">Pending</div>
                                    <h5 class="fw-bold text-warning mb-0" id="totalPending">0</h5>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Footer -->
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>

    </div>

</div>


<!-- ADD SUBJECT EVALUATION MODAL -->

<div class="modal fade"
     id="addEvaluationModal"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1">

    <div class="modal-dialog modal-md">

        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">

                <h5 class="modal-title fw-semibold">
                    Evaluate Previous Subject
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <!-- Body -->
            <div class="modal-body">

                <form class="row g-3"
                      id="evaluationForm"
                      action="<?php echo BASE_URL ?>/transferees/evaluation/store"
                      method="POST">

                    <input type="hidden"
                           name="student_id"
                           id="evaluation_student_id">

                    <input type="hidden"
                           name="requirement_file_id"
                           id="requirement_file_id">


                    <!-- PREVIOUS SCHOOL SUBJECT -->
                    <div class="col-12">

                        <h6 class="text-primary fw-semibold">
                            Previous School Subject
                        </h6>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Subject Code
                        </label>

                        <input type="text"
                               class="form-control"
                               name="previous_subject_code"
                               placeholder="e.g. IT101">

                    </div>


                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Subject Grade
                        </label>

                        <input type="text"
                               class="form-control"
                               name="previous_grade"
                               placeholder="e.g. 1.75 or 88"
                               required>

                    </div>


                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Subject Name
                        </label>

                        <input type="text"
                               class="form-control"
                               name="previous_subject_name"
                               placeholder="Enter previous subject name"
                               required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Units
                        </label>

                        <input type="number"
                               class="form-control"
                               name="previous_units"
                               min="0"
                               step="0.01"
                               placeholder="e.g. 3.00"
                               required>

                    </div>


                    <!-- CURRICULUM SUBJECT -->
                    <div class="col-12">

                        <hr>

                        <h6 class="text-primary fw-semibold">
                            Equivalent Curriculum Subject
                        </h6>

                    </div>


                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Select Subject
                        </label>

                        <select class="form-select"
                                name="curriculum_subject_id"
                                id="curriculum_subject_id"
                                required>

                            <option value="">
                                Select Curriculum Subject
                            </option>

                        </select>

                    </div>


                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Remarks
                        </label>

                        <textarea class="form-control"
                                  name="remarks"
                                  rows="3"
                                  placeholder="Enter evaluation remarks..."></textarea>

                    </div>

                </form>

            </div>


            <!-- Footer -->
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary px-4"
                        id="saveEvaluation">
                    Save Evaluation
                </button>

            </div>

        </div>

    </div>

</div>


<!-- SCRIPTS -->

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL = "<?php echo BASE_URL ?>";
</script>

<script src="<?php echo BASE_URL ?>/js/transferee_evaluation.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>