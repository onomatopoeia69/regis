<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>


<main class="main-content">
    <div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Reports Generation</h1>
             <p class="text-muted small">Generate instant reports from the system.</p>
        </div>

          <button class="btn btn-primary btn-sm" id="submitApprove">
                <i class="bi bi-plus-lg"></i> Generate a Report
            </button>

    </div>
  
    <div class="card shadow-sm">


    <div class="card-header d-flex justify-content-between align-items-center">

            <h3 class="fs-6 mb-0 text-primary">Report List</h3>

    </div>

        <!-- test -->

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

                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="text"
                        class="form-control form-control-sm"
                        placeholder="Search activity..."
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
                            <th>Report ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">
                    </tbody>

                </table>

            </div>

         

       <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold" id="pageInfo"></div>
            <div id="pagination" class="d-flex gap-2"></div>
        </div>


         </div>
        
       
    </div>


      </div>
    </div>        
    </div>
</main>

<div class="modal fade" id="submitReportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content shadow-sm">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                Generate Report 
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>

             <div class="modal-body">

               <form class="row g-3" id="addReportApprovalForm" action="<?php echo BASE_URL ?>/reports-approval/store" method="POST" enctype="multipart/form-data">

               <div class="col-md-12">
                        <label class="form-label">Report Types</label>
                        <select name="report_type" class="form-control" required>
                        <option value="">Select Reports</option>
                                <option value="">Courses</option>
                                <option value="">Curriculum</option>

                        </select>

                </div>

                <div class="col-md-12">
                        <label class="form-label">School Year</label>
                        <select name="school_year" class="form-control" required>
                        <option value="">Select School Year</option>
                        <?php foreach($sy as $school_year) {  ?>
                            <option value="<?= $school_year['id'] ?>"><?= $school_year['name'] ?? '' ?></option>
                         <?php }?>

                        </select>

                </div>



               </form>

             

            </div>

    


            <!-- Footer -->
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-primary px-4"
                        id="addReportSubmit">
                    Submit
                </button>

            </div>

        </div>
    </div>
</div>


</div>


<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>   const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?php echo BASE_URL ?>/js/reports.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?>
