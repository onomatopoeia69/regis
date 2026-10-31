  
  document.addEventListener('DOMContentLoaded', function() {
    
   const selectAll = document.getElementById('select-all');
   const tbody = document.querySelector('tbody');
   const deleteBtn = document.getElementById('delete-btn');
   const approveBtn = document.getElementById('verifyPaymentBtn');
   const rejectBtn = document.getElementById('rejectPaymentBtn');
   const submissionId = document.getElementById('paymentSubmissionIdInput');
   const paymentVerificationModal = new bootstrap.Modal(
    document.getElementById('paymentVerificationModal'),
    {
        focus: false
    }
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


    
 document.getElementById("studentsTableBody").addEventListener("click", async function(e) {

    const button = e.target.closest('.view-btn');

    if (!button) return;

    const id = button.dataset.id;

    try {

        const response = await fetch(
            `${BASE_URL}/enrollment/view/${id}`
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to load payment information.');
        }

        const enroll = result.data;

        // Student Information
        document.getElementById('paymentStudentName').textContent =
            `${enroll.first_name} ${enroll.middle_name || ''} ${enroll.surname}`;

        document.getElementById('paymentStudentNumber').textContent =
            enroll.student_number || '—';

        document.getElementById('paymentCourse').textContent =
            enroll.course_name || '—';

        document.getElementById('paymentYearLevel').textContent =
            formatYearLevel(enroll.year_level);

        document.getElementById('paymentSchoolYear').textContent =
            enroll.school_year || '—';

        document.getElementById('paymentSemester').textContent =
            enroll.semester || '—';

        // Submission Information
        document.getElementById('paymentSubmissionId').textContent =
            enroll.id || '—';

        document.getElementById('paymentSubmittedAt').textContent =
            enroll.submitted_at || '—';

        document.getElementById('paymentFileName').textContent =
            enroll.file_name || '—';

        document.getElementById('paymentRemarks').value =
            enroll.notes || '';

        document.getElementById('paymentSubmissionIdInput').value =
            enroll.id;

        // Payment Status
        const status = String(enroll.status || 'pending').toLowerCase();

        const statusElement = document.getElementById('paymentStatus');

        statusElement.textContent =
            status.charAt(0).toUpperCase() + status.slice(1);

        statusElement.className = 'badge ' + (
            status === 'verified'
                ? 'bg-success-subtle text-success-emphasis'
                : status === 'rejected'
                    ? 'bg-danger-subtle text-danger-emphasis'
                    : 'bg-warning-subtle text-warning-emphasis'
        );
        

        // Receipt Preview
        const image = document.getElementById('paymentReceiptPreview');
        const placeholder = document.getElementById('paymentReceiptPlaceholder');
        const receiptLink = document.getElementById('paymentReceiptLink');

        image.classList.add('d-none');
        image.src = '';

        placeholder.classList.remove('d-none');
        receiptLink.classList.add('d-none');

        if (enroll.file_data) {

            console.log(enroll.file_data);

         const receiptUrl = `${STUDENT_BASE_URL}/self-enrollment/view/${enroll.id}`;

        receiptLink.href = receiptUrl;
        receiptLink.classList.remove('d-none');

        if (enroll.file_mime?.startsWith('image/')) {
            image.src = receiptUrl;
            image.classList.remove('d-none');
            placeholder.classList.add('d-none');
        }else {
                placeholder.innerHTML = `
                    <i class="bi bi-file-earmark-pdf fs-1"></i>
                    <p class="mb-0">Receipt uploaded. Click View Full Receipt.</p>
                `;
            }
        }

        const isPending = status === 'pending';

        document.getElementById('verifyPaymentBtn').classList.toggle(
            'd-none', !isPending
        );

        document.getElementById('rejectPaymentBtn').classList.toggle(
            'd-none', !isPending
        );

        paymentVerificationModal.show();

    } catch (error) {

        console.error(error);

        Swal.fire('Error', error.message, 'error');
    }
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

    fetch(`${BASE_URL}/enrollment/info?status=${status}&order=${order}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">No result found</td></tr>`;
                return;
            }

            result.data.forEach(enroll => {
                tbody.innerHTML += `
                    <tr class="activity-row">

                     <td><input type="checkbox" class="activity-checkbox" value="${enroll.id}"></td>
                    <td>${enroll.first_name} ${enroll.surname} </td>
                     <td>${enroll.course_name} </td>
                     <td>${formatYearLevel(enroll.year_level)} </td>
                        <td>${enroll.school_year} </td>
                        <td>${enroll.semester}</td>

                        <td>
                            <button class="btn btn-sm btn-secondary view-btn" data-id="${enroll.id}">
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



function formatYearLevel(year) {
    const yearLevels = {
        1: '1st Year',
        2: '2nd Year',
        3: '3rd Year',
        4: '4th Year',
        5: '5th Year'
    };

    return yearLevels[year] || 'Unknown Year';
}








