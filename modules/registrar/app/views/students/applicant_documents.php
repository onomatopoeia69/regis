<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Applicant Document Requirements</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>


    </div>

      <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Requirement List</h3>

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
                        <option value="desc">Descending (Z-A)</option>
                        <option value="asc">Ascending (A-Z)</option>
                    </select>
                </div>

                <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="status">
                            <option value="">All Status</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>      

                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="text"
                        class="form-control form-control-sm"
                        placeholder="Search subjects..."
                        id="search">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-danger btn-sm d-none w-100" id="delete-btn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>

        </div>


        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Applicant Name</th>   
                            <th>Requirement</th>
                            <th>Submitted Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">
                    </tbody>

                </table>

            </div>


            
            <div class="d-flex align-items-center justify-content-between mt-3">

                <div class="fw-semibold small" id="pageInfo"></div>

                <div id="pagination" class="d-flex gap-1"></div>

            </div>

        </div>

    </div>




    </div>

</div>
</main>


<!-- Payment Verification Modal -->
 <div class="modal fade" id="requirementVerificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        Requirement Verification
                    </h5>
                    <small class="text-muted">Review and verify the applicant's submitted requirement</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Requirement Status -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-muted">Verification Status</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis" id="requirementStatus">
                        Pending
                    </span>
                </div>

                <!-- Applicant Information -->
                <h6 class="fw-bold mb-3">Applicant Information</h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Applicant Name</small>
                        <div class="fw-semibold" id="requirementApplicantName">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Applicant ID</small>
                        <div class="fw-semibold" id="requirementApplicantId">—</div>
                    </div>
                </div>

                <hr>

                <!-- Requirement Information -->
                <h6 class="fw-bold mb-3">Requirement Information</h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Requirement Name</small>
                        <div class="fw-semibold" id="requirementName">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Requirement ID</small>
                        <div class="fw-semibold" id="requirementId">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">File Name</small>
                        <div class="fw-semibold" id="requirementFileName">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Date Submitted</small>
                        <div class="fw-semibold" id="requirementSubmittedDate">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Created At</small>
                        <div class="fw-semibold" id="requirementCreatedAt">—</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Last Updated</small>
                        <div class="fw-semibold" id="requirementUpdatedAt">—</div>
                    </div>
                </div>

                <hr>

                <!-- Uploaded Document -->
                <h6 class="fw-bold mb-3">Uploaded Document</h6>

                <div class="border rounded p-2 text-center bg-body-tertiary">
                    <img id="requirementFilePreview"
                         class="img-fluid rounded d-none"
                         style="max-height: 500px;"
                         alt="Uploaded requirement">

                    <iframe id="requirementPdfPreview"
                            class="w-100 rounded d-none"
                            style="height: 550px; border: 0;"
                            title="Uploaded requirement PDF"></iframe>

                    <div id="requirementFilePlaceholder" class="py-5 text-muted">
                        <i class="bi bi-file-earmark-x fs-1"></i>
                        <p class="mb-0">No document uploaded</p>
                    </div>
                </div>

                <a href="#" id="requirementFileLink"
                   target="_blank"
                   rel="noopener"
                   class="btn btn-sm btn-outline-primary mt-2 d-none">
                    <i class="bi bi-download me-1"></i>
                    View / Download Document
                </a>

                <hr>

                <!-- Registrar Remarks -->
                <div class="mb-2">
                    <label for="requirementRemarks" class="form-label fw-semibold">
                        Registrar Remarks
                    </label>
                    <textarea id="requirementRemarks"
                              class="form-control"
                              rows="3"
                              placeholder="Enter verification remarks or rejection reason..."></textarea>
                    <small class="text-muted">
                        Remarks are required when rejecting a requirement.
                    </small>
                </div>

                <input type="hidden" id="requirementIdInput">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-danger" id="rejectRequirementBtn">
                    <i class="bi bi-x-circle me-1"></i>
                    Reject
                </button>

                <button type="button" class="btn btn-success" id="verifyRequirementBtn">
                    <i class="bi bi-check-circle me-1"></i>
                    Verify Requirement
                </button>
            </div>

        </div>
    </div>
</div>






<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script> const STUDENT_BASE_URL = "<?php echo STUDENT_BASE_URL ?>"</script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/document_requirements.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?> 