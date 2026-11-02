document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // CONFIGURATION
    // ==========================================

    const CURRICULUM_ID = window.CURRICULUM_ID || null;
    const APPLICANT_ID = window.APPLICANT_ID || null;

    const curriculumSelect = document.getElementById('curriculum_subject_id');
    const evaluationForm = document.getElementById('evaluationForm');
    const evaluationTable = document.getElementById('evaluationTable');

    // ==========================================
    // HELPERS
    // ==========================================

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

        const data = await response.json();

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

             console.log(subjects);

            curriculumSelect.innerHTML =
                '<option value="">Select equivalent subject</option>';

               

            // Supports a flat array or grouped year/semester response.
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

            if (!curriculumSelect.options.length ||
                curriculumSelect.options.length === 1) {

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

        evaluationForm.addEventListener('submit', async (event) => {

            event.preventDefault();

            const formData = new FormData(evaluationForm);

            const saveButton = evaluationForm.querySelector(
                '[type="submit"]'
            );

            if (saveButton) {
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
            }

            try {

                const response = await fetchJSON(
                    `${BASE_URL}/transferee/evaluation/store`,
                    {
                        method: 'POST',
                        body: formData
                    }
                );

                if (response.success) {

                    showMessage(
                        response.message || 'Evaluation saved successfully.'
                    );

                    evaluationForm.reset();

                    const modalElement =
                        document.getElementById('addEvaluationModal');

                    if (modalElement && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(
                            modalElement
                        ).hide();
                    }

                    loadEvaluations();

                } else {

                    showMessage(
                        response.message || 'Unable to save evaluation.',
                        'error'
                    );

                }

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

        if (!evaluationTable || !APPLICANT_ID) return;

        try {

            const evaluations = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/list/${APPLICANT_ID}`
            );

            const tbody = evaluationTable.querySelector('tbody');

            if (!tbody) return;

            tbody.innerHTML = '';

            if (!evaluations.length) {

                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No evaluation records found.
                        </td>
                    </tr>
                `;

                return;
            }

            evaluations.forEach(item => {

                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>${escapeHTML(item.previous_subject_code || '')}</td>
                    <td>${escapeHTML(item.previous_subject_name || '')}</td>
                    <td>${escapeHTML(item.curriculum_subject_name || 'Not matched')}</td>
                    <td>${escapeHTML(item.previous_grade ?? '')}</td>
                    <td>${escapeHTML(item.status || 'pending')}</td>
                    <td>
                        <button type="button"
                            class="btn btn-sm btn-outline-primary view-evaluation"
                            data-id="${Number(item.id)}">
                            View
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-success approve-evaluation"
                            data-id="${Number(item.id)}">
                            Approve
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger reject-evaluation"
                            data-id="${Number(item.id)}">
                            Reject
                        </button>
                    </td>
                `;

                tbody.appendChild(row);

            });

        } catch (error) {

            console.error('Load evaluations error:', error);

            showMessage(error.message, 'error');

        }
    }

    // ==========================================
    // VIEW EVALUATION
    // ==========================================

    async function viewEvaluation(id) {

        try {

            const result = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/view/${id}`
            );

            // Connect this to your View Evaluation modal.
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
            confirmButtonText: 'Yes, approve'
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

            showMessage(result.message || 'Evaluation approved.');

            loadEvaluations();

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
            confirmButtonText: 'Reject'
        });

        if (!result.isConfirmed) return;

        try {

            const response = await fetchJSON(
                `${BASE_URL}/transferees/evaluation/reject`,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        id: id,
                        remarks: result.value.trim()
                    })
                }
            );

            showMessage(response.message || 'Evaluation rejected.');

            loadEvaluations();

        } catch (error) {

            showMessage(error.message, 'error');

        }
    }

    // ==========================================
    // TABLE BUTTON EVENTS
    // ==========================================

    if (evaluationTable) {

        evaluationTable.addEventListener('click', event => {

            const button = event.target.closest('button[data-id]');

            if (!button) return;

            const id = button.dataset.id;

            if (button.classList.contains('view-evaluation')) {
                viewEvaluation(id);
            }

            if (button.classList.contains('approve-evaluation')) {
                approveEvaluation(id);
            }

            if (button.classList.contains('reject-evaluation')) {
                rejectEvaluation(id);
            }

        });

    }

    // ==========================================
    // HTML ESCAPE
    // ==========================================

    function escapeHTML(value) {

        return String(value).replace(/[&<>"']/g, char => ({
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
        console.error('PHP returned HTML or invalid JSON:', text);
        throw new Error(
            'Invalid server response. Check the browser Network tab for the PHP error.'
        );
    }

    if (!response.ok) {
        throw new Error(data.message || 'Something went wrong.');
    }

    return data;
}


    

 

    // ==========================================
    // INITIALIZE
    // ==========================================

    loadCurriculumSubjects();
    loadEvaluations();

});
