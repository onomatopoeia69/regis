

document.addEventListener('DOMContentLoaded', () => {
    loadApplications();

    document.getElementById('searchApplication')
        ?.addEventListener('input', debounce(() => loadApplications(1), 400));

    document.getElementById('statusFilter')
        ?.addEventListener('change', () => loadApplications(1));

    document.getElementById('courseFilter')
        ?.addEventListener('change', () => loadApplications(1));
});

async function loadApplications(page = 1) {
    const tbody = document.getElementById('applicationsTableBody');

    const params = new URLSearchParams({
        page,
        limit: 10,
        search: document.getElementById('searchApplication')?.value || '',
        status: document.getElementById('statusFilter')?.value || '',
        course: document.getElementById('courseFilter')?.value || '',
        order: 'DESC'
    });

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                Loading applications...
            </td>
        </tr>
    `;

    try {
        const response = await fetch(
            `${BASE_URL}/transferee-application/all?${params}`
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to retrieve applications.');
        }

        renderApplications(result.data);
        renderPagination(result.current_page, result.last_page);

        updateSummary(result.summary);

        

    } catch (error) {
        console.error(error);

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    ${escapeHTML(error.message)}
                </td>
            </tr>
        `;
    }
}

function renderApplications(applications) {
    const tbody = document.getElementById('applicationsTableBody');

    if (!applications || applications.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    No transferee applications found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = applications.map(app => {
        const status = app.evaluation_status || 'pending';

        const studentInfo = `
            <div class="fw-semibold">${escapeHTML(app.student_name)}</div>
            <small class="text-muted">
                ${escapeHTML(app.student_number || 'Not yet assigned')}
            </small>
        `;

        const torDocument = app.tor_file_id
            ? `<span class="text-success small">
                    <i class="bi bi-file-earmark-check"></i> TOR Uploaded
               </span>`
            : `<span class="text-danger small">
                    <i class="bi bi-file-earmark-x"></i> No TOR
               </span>`;

        const studentId = Number(app.student_id) || 0;
        const applicantId = Number(app.applicant_id) || 0;

        const evaluationUrl = studentId
            ? `${BASE_URL}/transferee/evaluation/${studentId}`
            : `${BASE_URL}/transferee/evaluation/${applicantId}`;

        return `
            <tr data-status="${escapeHTML(status)}"
                data-course="${escapeHTML(app.course_code || '')}">

                <td>${studentInfo}</td>

                <td>${escapeHTML(app.course_name || '—')}</td>

                <td>${escapeHTML(app.school_last_attended || '—')}</td>

                <td>${torDocument}</td>

                <td>${getStatusBadge(status)}</td>

                <td class="text-end">
                    <a href="${evaluationUrl}"
                       class="btn btn-sm btn-primary">
                        ${status === 'evaluated' || status === 'credited'
                            ? 'View Evaluation'
                            : 'Evaluate'}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusBadge(status) {
    const badges = {
        pending: 'warning',
        for_evaluation: 'primary',
        evaluated: 'success',
        credited: 'info'
    };

    const color = badges[status] || 'secondary';

    const label = status.replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase());

    return `
        <span class="badge bg-${color}-subtle text-${color}">
            ${escapeHTML(label)}
        </span>
    `;
}

function updateSummary(summary) {
    if (!summary) return;

    const cards = document.querySelectorAll('.summary-count');

    // Update these only if your summary elements have matching IDs.
    const elements = {
        pending: document.getElementById('summaryPending'),
        for_evaluation: document.getElementById('summaryForEvaluation'),
        evaluated: document.getElementById('summaryEvaluated'),
        credited: document.getElementById('summaryCredited')
    };

    Object.entries(elements).forEach(([key, element]) => {
        if (element) {
            element.textContent = summary[key] ?? 0;
        }
    });
}

function renderPagination(currentPage, lastPage) {
    const pagination = document.getElementById('applicationsPagination');

    if (!pagination) return;

    if (lastPage <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';

    for (let i = 1; i <= lastPage; i++) {
        html += `
            <li class="page-item ${i == currentPage ? 'active' : ''}">
                <button class="page-link"
                        type="button"
                        onclick="loadApplications(${i})">
                    ${i}
                </button>
            </li>
        `;
    }

    pagination.innerHTML = html;
}

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

function debounce(callback, delay) {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback(...args), delay);
    };
}


function updateSummary(summary) {
    if (!summary) return;

    document.getElementById('summaryPending').textContent =
        summary.pending ?? 0;

    document.getElementById('summaryForEvaluation').textContent =
        summary.for_evaluation ?? 0;

    document.getElementById('summaryEvaluated').textContent =
        summary.evaluated ?? 0;

    document.getElementById('summaryCredited').textContent =
        summary.credited ?? 0;
}