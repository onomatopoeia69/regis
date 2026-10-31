<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Enrollment</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>


    </div>

      <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Enrollment List</h3>

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
                            <th>Student Name</th>   
                            <th>Course</th>
                            <th>School Year</th>
                            <th>Year</th>
                            <th>Semester</th>
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
<div class="modal fade" id="paymentVerificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i>
                        Payment Verification
                    </h5>
                    <small class="text-muted">Review student payment submission</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Payment Status</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis"
                        id="paymentStatus">Pending</span>
                </div>

                <!-- Student Information -->
                <h6 class="fw-bold mb-3">Student Information</h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Student Name</small>
                        <div class="fw-semibold" id="paymentStudentName">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Student Number</small>
                        <div class="fw-semibold" id="paymentStudentNumber">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Course</small>
                        <div class="fw-semibold" id="paymentCourse">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Year Level</small>
                        <div class="fw-semibold" id="paymentYearLevel">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">School Year</small>
                        <div class="fw-semibold" id="paymentSchoolYear">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Semester</small>
                        <div class="fw-semibold" id="paymentSemester">-</div>
                    </div>
                </div>

                <hr>

                <!-- Submission Information -->
                <h6 class="fw-bold mb-3">Payment Submission</h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Submission ID</small>
                        <div class="fw-semibold" id="paymentSubmissionId">-</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Date Submitted</small>
                        <div class="fw-semibold" id="paymentSubmittedAt">-</div>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">File Name</small>
                        <div class="fw-semibold" id="paymentFileName">-</div>
                    </div>
                </div>

                <hr>

                <!-- Receipt Preview -->
                <h6 class="fw-bold mb-3">Uploaded Payment Receipt</h6>

                <div class="border rounded p-2 text-center bg-body-tertiary">
                    <img id="paymentReceiptPreview"
                        class="img-fluid rounded d-none"
                        style="max-height: 400px;"
                        alt="Payment receipt">

                    <div id="paymentReceiptPlaceholder" class="py-5 text-muted">
                        <i class="bi bi-file-earmark-image fs-1"></i>
                        <p class="mb-0">Receipt preview unavailable</p>
                    </div>
                </div>

                <a href="#" id="paymentReceiptLink"
                    target="_blank"
                    class="btn btn-sm btn-outline-primary mt-2 d-none">
                    <i class="bi bi-eye me-1"></i> View Full Receipt
                </a>

                <hr>

                <!-- Remarks -->
                <div class="mb-2">
                    <label class="form-label fw-semibold">
                        Verification Remarks
                    </label>
                    <textarea id="paymentRemarks"
                        class="form-control"
                        rows="3"
                        placeholder="Enter remarks..."></textarea>
                </div>

                <input type="hidden" id="paymentSubmissionIdInput">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light"
                    data-bs-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-danger" id="rejectPaymentBtn">
                    <i class="bi bi-x-circle me-1"></i> Reject
                </button>

                <button type="button" class="btn btn-success" id="verifyPaymentBtn">
                    <i class="bi bi-check-circle me-1"></i> Verify Payment
                </button>
            </div>

        </div>
    </div>
</div>






<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script> const STUDENT_BASE_URL = "<?php echo STUDENT_BASE_URL ?>"</script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/enrollment.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?> 