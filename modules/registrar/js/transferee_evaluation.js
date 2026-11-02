
document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // CONFIGURATION
    // ==========================================

    const CURRICULUM_ID = window.CURRICULUM_ID || null;
    const APPLICANT_ID = window.APPLICANT_ID || null;

    const curriculumSelect = document.getElementById('curriculum_subject_id');
    const evaluationForm = document.getElementById('evaluationForm');
    const evaluationTable = document.getElementById('evaluationTable');
    const tbody = document.getElementById('evaluationTableBody');
    const statusFilter = document.getElementById('evaluationStatus');
    const searchInput = document.getElementById('evaluationSearch');

    let evaluationSubjects = [];

    // ==========================================
    // HELPERS
    // ==========================================

    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    async function fetchJSON(url, options = {}) {

        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(options.body instanceof FormData
                    ? {}
                    : { 'Content-Type': 'application/json' }),
                ...options.headers
            }
        });

        const text = await response.text();

        let data;

        try {
            data = JSON.parse(text);
        } catch (error) {
            console.error('Invalid server response:', text);
            throw new Error('Invalid server response. Check your PHP error logs.');
        }

        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }

        return data;
    }

    function showMessage(message, type = 'success') {

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success' : 'Notice',
                text: message
            });
        }

        alert(message);
    }

    // ==========================================
    // LOAD CURRICULUM SUBJECTS
    // ==========================================

    async function loadCurriculumSubjects() {

        if (!curriculumSelect) return;

        if (!CURRICULUM_ID) {
            curriculumSelect.innerHTML =
                '<option value="">No active curriculum found</option>';
            return;
        }

        curriculumSelect.disabled = true;
        curriculumSelect.innerHTML =
            '<option value="">Loading subjects...</option>';

        try {

            const subjects = await fetchJSON(
                `${BASE_URL}/curriculum-subject/${CURRICULUM_ID}/all`
            );

            curriculumSelect.innerHTML =
                '<option value="">Select equivalent subject</option>';

            if (Array.isArray(subjects)) {

                subjects.forEach(subject => {
                    addSubjectOption(subject);
                });

            } else {

                Object.entries(subjects).forEach(([year, semesters]) => {

                    Object.entries(semesters).forEach(([semester, items]) => {

                        const group = document.createElement('optgroup');
                        group.label = `Year ${year} - ${semester}`;

                        items.forEach(subject => {

                            const option = document.createElement('option');

                            option.value = subject.id;
                            option.textContent =
                                `${subject.subject_code} - ${subject.subject_name} (${subject.subject_units} units)`;

                            group.appendChild(option);
                        });

                        curriculumSelect.appendChild(group);
                    });
                });
            }

            if (curriculumSelect.options.length <= 1) {
                curriculumSelect.innerHTML =
                    '<option value="">No curriculum subjects found</option>';
            }

        } catch (error) {

            console.error('Curriculum loading error:', error);

            curriculumSelect.innerHTML =
                '<option value="">Failed to load subjects</option>';

            showMessage(error.message, 'error');

        } finally {
            curriculumSelect.disabled = false;
        }
    }

    function addSubjectOption(subject) {

        const option = document.createElement('option');

        option.value = subject.id;

        option.textContent =
            `${subject.subject_code} - ${subject.subject_name} (Year ${subject.year_level})`;

        curriculumSelect.appendChild(option);
    }

    // ==========================================
    // ADD EVALUATION
    // ==========================================

    if (evaluationForm) {

        evaluationForm.addEventListener('submit', async event => {

            event.preventDefault();

            const formData = new FormData(evaluationForm);
            const saveButton = evaluationForm.querySelector('[type="submit"]');

            if (saveButton) {
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
            }

            try {

                const result = await fetchJSON(
                    `${BASE_URL}/transferee/evaluation/store`,
                    {
                        method: 'POST',
                        body: formData
                    }
                );

                if (!result.success) {
                    throw new Error(result.message || 'Unable to save evaluation.');
                }

                await showMessage(
                    result.message || 'Evaluation saved successfully.'
                );

                evaluationForm.reset();

                const modalElement =
                    document.getElementById('addEvaluationModal');

                if (modalElement && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                }

                loadEvaluations();

            } catch (error) {

                console.error('Save evaluation error:', error);
                showMessage(error.message, 'error');

            } finally {

                if (saveButton) {
                    saveButton.disabled = false;
                    saveButton.textContent = 'Save Evaluation';
                }
            }
        });
    }

    // ==========================================
    // LOAD EVALUATIONS
    // ==========================================

    async function loadEvaluations() {

        if (!tbody || !APPLICANT_ID) {
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Applicant ID is missing.
                        </td>
                    </tr>
                `;
            }
            return;
        }

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm"></div>
                    Loading evaluations...
                </td>
            </tr>
        `;

        try {

            const result = await fetchJSON(
                `${BASE_URL}/transferee/evaluation/subject/${encodeURIComponent(APPLICANT_ID)}`
            );

            if (!Array.isArray(result)) {
                throw new Error(result.message || 'Invalid evaluation response.');
            }

            evaluationSubjects = result;

            renderEvaluationSubjects();

        } catch (error) {

            console.error('Load evaluations error:', error);

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        ${escapeHTML(error.message)}
                    </td>
                </tr>
            `;
        }
    }

    // ==========================================
    // RENDER AND FILTER TABLE
    // ==========================================

    function renderEvaluationSubjects() {

        if (!tbody) return;

        const selectedStatus = statusFilter?.value.toLowerCase() || '';
        const searchTerm = searchInput?.value.trim().toLowerCase() || '';

        const filteredSubjects = evaluationSubjects.filter(subject => {

            const matchesStatus =
                !selectedStatus ||
                subject.status?.toLowerCase() === selectedStatus;

            const searchableText = [
                subject.previous_subject_code,
                subject.previous_subject_name,
                subject.equivalent_subject_code,
                subject.equivalent_subject_name,
                subject.curriculum_subject_code,
                subject.curriculum_subject_name,
                subject.previous_grade,
                subject.remarks
            ].join(' ').toLowerCase();

            const matchesSearch =
                !searchTerm || searchableText.includes(searchTerm);

            return matchesStatus && matchesSearch;
        });

        if (!filteredSubjects.length) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-4 d-block mb-2"></i>
                        No matching evaluated subjects found.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML = filteredSubjects.map(subject => {

            const id = Number(subject.evaluation_id ?? subject.id);
            const status = String(subject.status || 'pending').toLowerCase();

            const badgeClass = {
                pending: 'bg-warning text-dark',
                approved: 'bg-success',
                rejected: 'bg-danger'
            }[status] || 'bg-secondary';

            const isPending = status === 'pending';

            return `
                <tr>
                    <td>
                        <div class="fw-semibold">
                            ${escapeHTML(subject.previous_subject_name)}
                        </div>
                        <small class="text-muted">
                            ${escapeHTML(subject.previous_subject_code || 'No code')}
                        </small>
                    </td>

                    <td>
                        <div class="fw-semibold">
                            ${escapeHTML(
                                subject.equivalent_subject_name ||
                                subject.curriculum_subject_name ||
                                'Not assigned'
                            )}
                        </div>
                        <small class="text-muted">
                            ${escapeHTML(
                                subject.equivalent_subject_code ||
                                subject.curriculum_subject_code ||
                                '-'
                            )}
                        </small>
                    </td>

                    <td>${escapeHTML(subject.previous_grade)}</td>

                    <td>${escapeHTML(subject.previous_units)}</td>

                    <td>
                        <span class="badge ${badgeClass}">
                            ${escapeHTML(status)}
                        </span>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary px-2 py-1"
                                data-action="view"
                                data-id="${id}"
                                title="View evaluation">
                                <i class="bi bi-eye"></i>
                            </button>

                           

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger px-2 py-1"
                                data-action="delete"
                                data-id="${subject.evaluation_id}"
                                title="Delete evaluation"
                                ${!isPending ? 'disabled' : ''}>
                                <i class="bi bi-trash"></i>
                            </button>

                        </div>
                    </td>
                </tr>
            `;

        }).join('');
    }

    // ==========================================
    // VIEW EVALUATION
    // ==========================================

    async function viewEvaluation(id) {

        try {

            const result = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/view/${id}`
            );

            console.log('Evaluation details:', result);

            showMessage('Evaluation details loaded.');

        } catch (error) {

            showMessage(error.message, 'error');
        }
    }

    // ==========================================
    // APPROVE EVALUATION
    // ==========================================

    async function approveEvaluation(id) {

        const confirmation = await Swal.fire({
            title: 'Approve Evaluation?',
            text: 'Are you sure you want to approve this subject?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve',
            confirmButtonColor: '#198754'
        });

        if (!confirmation.isConfirmed) return;

        try {

            const result = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/approve`,
                {
                    method: 'POST',
                    body: JSON.stringify({ id })
                }
            );

            if (!result.success) {
                throw new Error(result.message || 'Unable to approve evaluation.');
            }

            await showMessage(result.message || 'Evaluation approved.');

            loadEvaluations();
            loadCurriculumSubjects();

        } catch (error) {

            showMessage(error.message, 'error');
        }
    }

    // ==========================================
    // REJECT EVALUATION
    // ==========================================

    async function rejectEvaluation(id) {

        const result = await Swal.fire({
            title: 'Reject Evaluation',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Enter your remarks...',
            inputAttributes: {
                'aria-label': 'Rejection remarks'
            },
            inputValidator: value => {
                if (!value || !value.trim()) {
                    return 'Please provide a reason for rejection.';
                }
            },
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {

            const response = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/reject`,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        id,
                        remarks: result.value.trim()
                    })
                }
            );

            if (!response.success) {
                throw new Error(response.message || 'Unable to reject evaluation.');
            }

            await showMessage(response.message || 'Evaluation rejected.');

            loadEvaluations();

        } catch (error) {

            showMessage(error.message, 'error');
        }
    }

    // ==========================================
    // DELETE EVALUATION
    // ==========================================

    async function deleteSubjectEvaluation(id) {

        const confirmation = await Swal.fire({
            title: 'Delete Subject Evaluation?',
            text: 'This pending evaluation will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        });

        if (!confirmation.isConfirmed) return;

        try {

            const result = await fetchJSON(
                `${BASE_URL}/transferee/evaluation/${id}/delete`,
                {
                    method: 'POST'
                }
            );

            if (!result.success) {
                throw new Error(result.message || 'Failed to delete evaluation.');
            }

            await Swal.fire({
                title: 'Deleted!',
                text: result.message || 'Evaluation deleted successfully.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });

            // Refresh the same table
            await loadEvaluations();

        } catch (error) {

            console.error('Delete evaluation error:', error);

            showMessage(error.message, 'error');
        }
    }

    // delete 

    tbody.addEventListener('click', function (event) {

    const button = event.target.closest('[data-action]');

    if (!button) return;

    const action = button.dataset.action;
    const id = button.dataset.id;

    if (action === 'delete') {
        deleteSubjectEvaluation(id);
    }

    });

    // ==========================================
    // TABLE BUTTON EVENTS
    // ==========================================

    if (evaluationTable) {

        evaluationTable.addEventListener('click', event => {

            const button = event.target.closest('button[data-action]');

            if (!button || button.disabled) return;

            const id = Number(button.dataset.id);
            if (!Number.isInteger(id) || id <= 0) return;

            const action = button.dataset.action;

            switch (action) {
                case 'view':
                    viewEvaluation(id);
                    break;

                case 'approve':
                    approveEvaluation(id);
                    break;

                case 'reject':
                    rejectEvaluation(id);
                    break;

                case 'delete':
                    deleteSubjectEvaluation(id);
                    break;
            }
        });
    }

    // ==========================================
    // FILTER EVENTS
    // ==========================================

    statusFilter?.addEventListener('change', renderEvaluationSubjects);

    searchInput?.addEventListener('input', renderEvaluationSubjects);

    // ==========================================
    // INITIALIZE
    // ==========================================

    loadCurriculumSubjects();
    loadEvaluations();

});
