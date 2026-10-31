  
  document.addEventListener('DOMContentLoaded', function() {
    
   const selectAll = document.getElementById('select-all');
   const tbody = document.querySelector('tbody');
   const deleteBtn = document.getElementById('delete-btn');
     const approveBtn = document.getElementById('verifyRequirementBtn');
    const rejectBtn = document.getElementById('rejectRequirementBtn');
    const requirementIdInput = document.getElementById('requirementIdInput');

    const requirementModalElement = document.getElementById('requirementVerificationModal');

    const requirementVerificationModal = new bootstrap.Modal(
        requirementModalElement,
        { focus: false }
    );

    let currentStatus = 'pending';
    let currentOrder = 'desc';
    let currentLimit = 10;
    let currentPage = 1;


    getData(currentStatus,currentOrder, currentLimit, currentPage);


    document.getElementById("status").addEventListener('change', (e) => {

        currentStatus = e.target.value;
        currentPage = 1;
        getData(currentStatus,currentOrder, currentLimit,currentPage);

    });




    document.getElementById("order").addEventListener('change', (e) => {

        currentOrder = e.target.value;
        currentPage = 1;
         getData(currentStatus,currentOrder, currentLimit, currentPage);
    });


    
    document.getElementById("limit").addEventListener('change', (e) => {


        currentLimit = e.target.value;
        currentPage = 1; 

         getData(currentStatus,currentOrder, currentLimit, currentPage);

    });


    document.getElementById("search").addEventListener("input", function(e) {

    const order = document.getElementById("order").value;
    const limit = document.getElementById("limit").value;
    const status = document.getElementById("status").value;
    
    getData(status,order, limit, 1); 

    });



    document.getElementById('pdf').addEventListener('click',()=>{

        const order = document.getElementById("order").value;
         getPdf(order);

    });

    document.getElementById('excel').addEventListener('click',()=>{

       const order = document.getElementById("order").value;
       getExcel(order);

    });

    document.getElementById('csv').addEventListener('click',()=>{

       const order = document.getElementById("order").value;
       getCsv(order);

    });



      /*  ========================================================================================= 
       |                                                                                         |
       |     Buttons                                                                             |
       |                                                                                         |
       ========================================================================================= 
    */

    
    // show and hide the delete button when there is checked box.

    function updateDeleteButton() {
        const checkedCount = tbody.querySelectorAll('.activity-checkbox:checked').length;
        deleteBtn.classList.toggle('d-none', checkedCount === 0);
    }

    // reset the checkbox and button delete
    function resetAll()
    {
        selectAll.checked =false;
        deleteBtn.classList.add('d-none');    
    }



    tbody.addEventListener('change', function(e) {
        if(e.target.classList.contains('activity-checkbox')) {
            updateDeleteButton();
        }
    });

    selectAll.addEventListener('change', function() {

        const allChildren = tbody.querySelectorAll('.activity-checkbox');

        allChildren.forEach(checkbox => {
            checkbox.checked = this.checked;
        });

        updateDeleteButton(); 
    });



    deleteBtn.addEventListener('click', function(){

        const ids = Array.from(
            tbody.querySelectorAll('.activity-checkbox:checked')
        ).map(cb => cb.value);


        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: `Yes, delete (${ids.length}) item/s!`,
            }).then((result) => {
            if (result.isConfirmed) {

                fetch(`${BASE_URL}/course/delete`, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        Swal.fire({
                            title: "Deleted!",
                            text: "Item/s has been deleted.",
                            icon: "success"
                            });

                        resetAll();
                         getData(currentStatus,currentOrder, currentLimit, currentPage);
                    } else {
                        alert('Delete failed!');
                    }
                });
                
            }
            });

        });

     /*  ========================================================================================= 
       |                                                                                         |
       |     Add Modal                                                                           |
       |                                                                                         |
       ========================================================================================= 
    */
    


// APPROVE
approveBtn.addEventListener('click', async function () {


    const id = submissionId.value;

    const confirm = await Swal.fire({
        title: 'Approve Payment?',
        text: 'Are you sure you want to approve this payment receipt?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel'
    });

    if (!confirm.isConfirmed) return;

    try {
        const response = await fetch(`${BASE_URL}/enrollment/approve/${id}`, {
            method: 'POST'
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message);
        }

        await Swal.fire('Approved!', result.message, 'success');

        bootstrap.Modal.getInstance(
            document.getElementById('paymentVerificationModal')
        )?.hide();

        
        

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
});


rejectBtn.addEventListener('click', async function () {

    const id = submissionId.value;

    const result = await Swal.fire({
        title: 'Reject Payment?',
        text: 'Please provide a reason for rejecting this payment.',
        input: 'textarea',
        inputPlaceholder: 'Type your rejection reason here...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
            'rows': '5'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject Payment',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        allowOutsideClick: false,
        preConfirm: (reason) => {
            if (!reason || !reason.trim()) {
                Swal.showValidationMessage('Please enter a rejection reason.');
                return false;
            }

            return reason.trim();
        }
    });

    if (!result.isConfirmed) return;

    const reason = result.value;

    try {
        const response = await fetch(`${BASE_URL}/enrollment/reject/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                notes: reason
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to reject payment.');
        }

        await Swal.fire('Rejected!', data.message, 'success');

        bootstrap.Modal.getInstance(
            document.getElementById('paymentVerificationModal')
        )?.hide();

         getData(currentStatus,currentOrder, currentLimit, currentPage);

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
});

       

    // show  


    



tbody.addEventListener('click', async function (e) {

    const button = e.target.closest('.view-btn');

    if (!button) return;

    const id = button.dataset.id;

    // Reset modal
    requirementIdInput.value = id;

    document.getElementById('requirementApplicantName').textContent = 'Loading...';
    document.getElementById('requirementApplicantId').textContent = '—';
    document.getElementById('requirementName').textContent = 'Loading...';
    document.getElementById('requirementId').textContent = '—';
    document.getElementById('requirementFileName').textContent = '—';
    document.getElementById('requirementSubmittedDate').textContent = '—';
    document.getElementById('requirementCreatedAt').textContent = '—';
    document.getElementById('requirementUpdatedAt').textContent = '—';
    document.getElementById('requirementRemarks').value = '';

    const image = document.getElementById('requirementFilePreview');
    const pdf = document.getElementById('requirementPdfPreview');
    const placeholder = document.getElementById('requirementFilePlaceholder');
    const fileLink = document.getElementById('requirementFileLink');

    image.classList.add('d-none');
    image.src = '';

    pdf.classList.add('d-none');
    pdf.src = '';

    placeholder.classList.remove('d-none');
    placeholder.innerHTML = `
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 mb-0">Loading document...</p>
    `;

    fileLink.classList.add('d-none');

    requirementVerificationModal.show();

    try {

        const response = await fetch(
            `${BASE_URL}/requirements/view/${id}`
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to load requirement.');
        }

        const requirement = result.data;

        // Applicant information
        document.getElementById('requirementApplicantName').textContent =
            [
                requirement.first_name,
                requirement.middle_name,
                requirement.surname
            ].filter(Boolean).join(' ') || '—';

        document.getElementById('requirementApplicantId').textContent =
            requirement.applicant_id || '—';

        // Requirement information
        document.getElementById('requirementName').textContent =
            requirement.requirement_name || '—';

        document.getElementById('requirementId').textContent =
            requirement.requirement_id || '—';

        document.getElementById('requirementFileName').textContent =
            requirement.file_name || 'No file uploaded';

        document.getElementById('requirementSubmittedDate').textContent =
            requirement.submitted_date || 'Not submitted';

        document.getElementById('requirementCreatedAt').textContent =
            requirement.created_at || '—';

        document.getElementById('requirementUpdatedAt').textContent =
            requirement.updated_at || '—';

        // Status
        const status = String(requirement.status || 'pending').toLowerCase();

        const statusElement = document.getElementById('requirementStatus');

        statusElement.textContent =
            status.charAt(0).toUpperCase() + status.slice(1);

        statusElement.className = 'badge ' + (
            status === 'verified'
                ? 'bg-success-subtle text-success-emphasis'
                : status === 'rejected'
                    ? 'bg-danger-subtle text-danger-emphasis'
                    : 'bg-warning-subtle text-warning-emphasis'
        );

        // Existing remarks
        document.getElementById('requirementRemarks').value =
            requirement.notes || '';

        // Document preview
        if (
            Number(requirement.is_submitted) === 1 &&
            requirement.file_name
        ) {

            const fileUrl = `${BASE_URL}/requirements/file/${id}`;
            const mime = String(requirement.file_mime || '').toLowerCase();

            fileLink.href = fileUrl;
            fileLink.classList.remove('d-none');

            if (mime.startsWith('image/')) {

                image.src = fileUrl;
                image.classList.remove('d-none');
                placeholder.classList.add('d-none');

            } else if (mime === 'application/pdf') {

                pdf.src = fileUrl;
                pdf.classList.remove('d-none');
                placeholder.classList.add('d-none');

            } else {

                placeholder.classList.remove('d-none');
                placeholder.innerHTML = `
                    <i class="bi bi-file-earmark fs-1"></i>
                    <p class="mb-0">Preview is not available for this file type.</p>
                `;
            }

        } else {

            placeholder.classList.remove('d-none');
            placeholder.innerHTML = `
                <i class="bi bi-file-earmark-x fs-1"></i>
                <p class="mb-0">The applicant has not uploaded this document.</p>
            `;
        }

        // Only pending submitted requirements can be verified or rejected
        const canVerify =
            status === 'pending' &&
            Number(requirement.is_submitted) === 1 

        approveBtn.classList.toggle('d-none', !canVerify);
        rejectBtn.classList.toggle('d-none', !canVerify);

        // Remarks are editable only while pending
        document.getElementById('requirementRemarks').readOnly =
            status !== 'pending';

    } catch (error) {

        console.error(error);

        placeholder.classList.remove('d-none');
        placeholder.innerHTML = `
            <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
            <p class="mb-0">Unable to load document information.</p>
        `;

        Swal.fire('Error', error.message, 'error');
    }
});


approveBtn.addEventListener('click', async function () {

    const id = requirementIdInput.value;

    if (!id) return;

    const confirm = await Swal.fire({
        title: 'Verify Requirement?',
        text: 'Are you sure this submitted document is valid?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, verify',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754'
    });

    if (!confirm.isConfirmed) return;

    try {

        approveBtn.disabled = true;

        const response = await fetch(
            `${BASE_URL}/requirements/approve/${id}`,
            {
                method: 'POST'
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to verify requirement.');
        }

        await Swal.fire(
            'Verified!',
            result.message || 'Requirement has been verified successfully.',
            'success'
        );

        requirementVerificationModal.hide();

        getData(
            currentStatus,
            currentOrder,
            currentLimit,
            currentPage
        );

    } catch (error) {

        console.error(error);

        Swal.fire('Error', error.message, 'error');

    } finally {

        approveBtn.disabled = false;

    }
});


rejectBtn.addEventListener('click', async function () {

    const id = requirementIdInput.value;

    if (!id) return;

    const result = await Swal.fire({
        title: 'Reject Requirement?',
        text: 'Please provide a reason for rejecting this document.',
        input: 'textarea',
        inputPlaceholder: 'Enter your rejection reason...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
            'rows': '5'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject Requirement',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        allowOutsideClick: false,

        preConfirm: (reason) => {

            if (!reason || !reason.trim()) {
                Swal.showValidationMessage(
                    'Please provide a rejection reason.'
                );

                return false;
            }

            return reason.trim();
        }
    });

    if (!result.isConfirmed) return;

    const reason = result.value;

    try {

        rejectBtn.disabled = true;

        const response = await fetch(
            `${BASE_URL}/requirements/reject/${id}`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    notes: reason
                })
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to reject requirement.');
        }

        await Swal.fire(
            'Rejected!',
            data.message || 'Requirement has been rejected.',
            'success'
        );

        requirementVerificationModal.hide();

        getData(
            currentStatus,
            currentOrder,
            currentLimit,
            currentPage
        );

    } catch (error) {

        console.error(error);

        Swal.fire('Error', error.message, 'error');

    } finally {

        rejectBtn.disabled = false;

    }
});


// Clear PDF/image preview when modal closes
requirementModalElement.addEventListener('hidden.bs.modal', function () {

    document.getElementById('requirementFilePreview').src = '';
    document.getElementById('requirementPdfPreview').src = '';

});


     


  });
  


     /*  ========================================================================================= 
       |                                                                                         |
       |     End of DOM Content                                                                  |
       |                                                                                         |
       ========================================================================================= 
    */



  


    function resetForm(formId)
    {

        const form = document.getElementById(formId); 

        form.reset(); 
        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));


    }


    // pdf data 

    function getPdf(order)
    {
        const search = document.getElementById("search").value;

        //  fetch(`${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`)
        // .then(response => response.blob())
        // .then(blob => {
        //     const url = URL.createObjectURL(blob);
        //     window.open(url); 
        // })
        // .catch(err => console.error(err));
        

        // take this if you dont want redirection confirmation 

        window.open(
        `${BASE_URL}/course/pdf?order=${order}&search=${encodeURIComponent(search)}`,
        "_blank"
        );
    }


    function getExcel(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/course/excel?range=${order}&search=${encodeURIComponent(search)}`;
    }


    function getCsv(order)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/course/csv?order=${order}&search=${encodeURIComponent(search)}`;
    }




    // get data from students 

    function getData(status, order, limit, page = 1){

    const search = document.getElementById("search").value;
    const tbody = document.getElementById("studentsTableBody");

    fetch(`${BASE_URL}/requirements/all?status=${status}&order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">No result found</td></tr>`;
                return;
            }

            result.data.forEach(approval => {
                tbody.innerHTML += `
                    <tr class="activity-row">

                     <td><input type="checkbox" class="activity-checkbox" value="${approval.id}"></td>
                    <td>${approval.first_name} ${approval.surname} </td>
                     <td>${approval.requirement_name} </td>
                        <td>${approval.created_at}</td>

                        <td>
                            <button class="btn btn-sm btn-secondary view-btn" data-id="${approval.id}">
                                View
                            </button>
                        
                        </td>
                        
                    </tr>
                `;
            });

            const rows = tbody.querySelectorAll(".activity-row");

            if (rows.length > 0) {
                gsap.from(rows, {
                x: -200,          
                opacity: 0,       
                duration: 0.5,
                stagger: 0.3,      
                ease: "power2.out" 
                });
            }

            renderPagination(result.current_page, result.last_page);
            renderResultInfo(result);

        });
}

function renderPagination(current, last) {

    
    const container = document.getElementById("pagination");
    container.innerHTML = "";

    if (last <= 1) return;

    for (let i = 1; i <= last; i++) {

    const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = "btn btn-sm " + (i === current ? "btn-primary" : "btn-outline-primary");
        
        btn.addEventListener("click", function() {

            const status = document.getElementById("order").value;
            const limit = document.getElementById("limit").value;

            getData(status, limit, i);

        });

        container.appendChild(btn);
    }

    
}


function renderResultInfo(result) {

    const info = document.getElementById("pageInfo");

    if (result.total === 0) {
        info.textContent = "No results found";
        return;
    }

    const start = (result.current_page - 1) * result.data.length + 1;
    let end = result.current_page * result.data.length;

    if (end > result.total) {
        end = result.total;
    }

    info.textContent = `Showing ${start}–${end} of ${result.total} results`;
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

function getStatusBadge(status) {

    const badges = {
        pending: 'bg-warning-subtle text-warning-emphasis',
        verified: 'bg-success-subtle text-success-emphasis',
        rejected: 'bg-danger-subtle text-danger-emphasis'
    };

    const color = badges[String(status).toLowerCase()]
        || 'bg-secondary-subtle text-secondary-emphasis';

    return `
        <span class="badge ${color}">
            ${escapeHTML(status || 'Unknown')}
        </span>
    `;
}










