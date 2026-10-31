<?php include __DIR__ . '/partials/sidebar.php'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

<main class="main-content bg-light pb-5">
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <small class="text-primary fw-semibold">Dashboard <i class="bi bi-chevron-right mx-1 small text-muted"></i> Self Enrollment</small>
                <h2 class="fw-bold mt-1 text-dark">Self Enrollment</h2>
                <p class="text-muted mb-0">Upload your proof of payment.</p>
            </div>
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-plus-lg me-1"></i> Add File
            </button>
        </div>

        <div id="upload-alert" class="alert d-none" role="alert"></div>

        <h5 class="fw-bold mb-3">Submitted Documents</h5>

        <?php if (empty($uploads)): ?>
            <div class="alert alert-info">Wala ka pang naipasang dokumento. I-click ang "Add File" para mag-upload.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($uploads as $upload): ?>
                    <li class="list-group-item py-2 d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <a href="<?= BASE_URL ?>/self-enrollment/view/<?= (int) $upload['id'] ?>"
                               target="_blank" rel="noopener" class="text-decoration-none">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <?= htmlspecialchars($upload['file_name']) ?>
                            </a>
                            <?php if (!empty($upload['status'])): ?>
                                <?php
                                    $statusClass = [
                                        'pending' => 'text-bg-warning',
                                        'verified' => 'text-bg-success',
                                        'rejected' => 'text-bg-danger',
                                    ][$upload['status']] ?? 'text-bg-secondary';
                                ?>
                                <span class="badge <?= $statusClass ?> ms-1"><?= htmlspecialchars(ucfirst($upload['status'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/self-enrollment/view/<?= (int) $upload['id'] ?>"
                               target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="<?= (int) $upload['id'] ?>"
                                    data-name="<?= htmlspecialchars($upload['file_name']) ?>">
                                <i class="bi bi-archive"></i> Archive
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="uploadModalLabel">Add File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="requirement-upload" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="file_name">File name</label>
                        <input class="form-control" id="file_name" type="text" name="file_name"
                               placeholder="e.g. My Payment Receipt" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="file">Select file</label>
                        <input class="form-control" id="file" type="file" name="file"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">PDF, JPG, or PNG. Maximum 5 MB.</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="notes">Notes</label>
                        <input class="form-control" id="notes" type="text" name="notes"
                               placeholder="Optional notes">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Upload
document.querySelectorAll('.requirement-upload').forEach(function (form) {
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const alertBox = document.getElementById('upload-alert');
        button.disabled = true;
        button.textContent = 'Uploading...';

        try {
            const response = await fetch('<?= BASE_URL ?>/self-enrollment/upload', {
                method: 'POST',
                body: new FormData(form)
            });
            const result = await response.json();
            alertBox.className = 'alert ' + (result.success ? 'alert-success' : 'alert-danger');
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            if (result.success) window.location.reload();
        } catch (error) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'The file could not be uploaded.';
            alertBox.classList.remove('d-none');
        } finally {
            button.disabled = false;
            button.textContent = 'Upload';
        }
    });
});

// Archive (soft delete)
document.querySelectorAll('.btn-delete').forEach(function (button) {
    button.addEventListener('click', async function () {
        const uploadId = button.dataset.id;
        const fileName = button.dataset.name || 'this file';

        if (!confirm('Archive "' + fileName + '"? The file will no longer be visible to you, but it will be kept in the system for record purposes.')) {
            return;
        }

        const alertBox = document.getElementById('upload-alert');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const formData = new FormData();
            formData.append('upload_id', uploadId);

            const response = await fetch('<?= BASE_URL ?>/self-enrollment/delete', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            alertBox.className = 'alert ' + (result.success ? 'alert-success' : 'alert-danger');
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            if (result.success) window.location.reload();
        } catch (error) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'The file could not be archived.';
            alertBox.classList.remove('d-none');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-archive"></i> Archive';
        }
    });
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>