

const CurrId = CurriculumID;



document.addEventListener('DOMContentLoaded', function() {


     const addCurrModal = new bootstrap.Modal(document.getElementById('curriculumSubjectModal'));
    
 
    getAll(CurrId);

    // pdf

    document.getElementById('pdf').addEventListener('click',()=>{

        getPdf(CurrId);

    });

    // open the manage pre-requisite
    document.getElementById('managePrerequisitesBtn')
        .addEventListener('click', function () {

            loadPrerequisiteSubjects(CurrId);

            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('managePrerequisitesModal')
            ).show();
        });


    // submit
    document.getElementById('managePrerequisitesForm')
    .addEventListener('submit', function (e) {

        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        fetch(`${BASE_URL}/curriculum/subjects/prerequisites/save`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (data.status === 'success') {

                bootstrap.Modal.getInstance(
                    document.getElementById('managePrerequisitesModal')
                ).hide();

                form.reset();

                $('#targetCurriculumSubject').val(null).trigger('change');
                $('#prerequisiteSelect').val(null).trigger('change');

                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success'
                });

            } else {

                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error(error);

            Swal.fire({
                title: 'Error!',
                text: 'Unable to save prerequisites.',
                icon: 'error'
            });
        });
    });

     // modal for curriclum subject
    document.getElementById('addCurrBtn').addEventListener('click', function(){
        addCurrModal.show();
    })
  
     document.getElementById('closeBtn').addEventListener('click',function(){

    resetForm('curriculumSubjectForm');

    });
 

     // add curr sub form 

     document.getElementById('curriculumSubjectForm').addEventListener('submit', function(e) {
     e.preventDefault(); 

        const formData = new FormData(this);

        

        fetch( this.action, { 
            method: 'POST',    
            body: formData,
        })
        .then(res => res.json())
        .then(data => {

        document.querySelectorAll('.error').forEach(el => el.innerText = '');

        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid');
        });

        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');

        if (data.status === 'error') {

             Swal.fire({
                title: "Error!",
                text: data.errors.subject_id,
                icon: data.status
             });


        } else if (data.status === 'success') {

            const form = document.getElementById('curriculumSubjectForm'); 

            addCurrModal.hide();
        
            
            form.reset();
            document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

              $('#subjectSelect').val(null).trigger('change');


            document.querySelectorAll(".subject-option").forEach(option => {
             option.style.display = "block";
            });

             getAll(CurrId);


            Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success"
             });

        }

        })
        .catch(err => console.log(err));
    });

    

     // for the  submit btn
    document.getElementById('curriculumSubjectSubmitBtn').addEventListener('click', function() {

    document.getElementById('curriculumSubjectForm').requestSubmit();
       
    });

    


    /// deletion of the curriculum subject

    
     document.getElementById("curriculum-structure").addEventListener("click", function(e) {

         if (e.target.classList.contains("del-btn")) {
  
         const currSubID = e.target.dataset.id;    
  
      

                fetch(`${BASE_URL}/curriculum-subject/delete`, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ currSubID })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        Swal.fire({
                            title: "Deleted!",
                            text: "Item has been removed.",
                            icon: "success"
                            });

                        getAll(CurrId);
                    } else {
                        alert('Delete failed!');
                    }
                });
         


       }


     });
    
   
   


 });


   /*  ========================================================================================= 
       |                                                                                         |
       |     End of DOM Content                                                                  |
       |                                                                                         |
       ========================================================================================= 
    */



 function getAll(CurrId)
 {
     fetch(`${BASE_URL}/curriculum-subject/${CurrId}/all`)
    .then(res => res.json())
    .then(data => {

    if (Object.keys(data).length === 0) {
        document.getElementById('curriculum-structure').innerHTML = 
        `<div class="text-center py-4 border border-dashed rounded-3 bg-light bg-opacity-25 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 text-secondary opacity-50" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <p class="mb-0 small fw-medium">No subjects allocated yet. Click "Add Subject" to populating the block.</p>
        </div>`;
         return;
    }
    
     renderCurriculum(data);

      const rows = document.querySelectorAll(".activity-row");

            if (rows.length > 0) {
                gsap.from(rows, {
                x: -200,          
                opacity: 0,       
                duration: 0.5,
                stagger: 0.3,      
                ease: "power2.out" 
                });
    }


    });

 }

   function resetForm(formId)
    {

        const form = document.getElementById(formId); 

        form.reset(); 
        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

        // for select2
      $('#subjectSelect').val(null).trigger('change');


    document.querySelectorAll(".subject-option").forEach(option => {
        option.style.display = "block";
    });

    }


 function renderCurriculum(data)
{
    let html = '';

    for (const year in data)
    {
        const yearLevel = yearParser(year);

        html += `
            <div class="mb-5">

                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                    <h4 class="fw-bold text-dark mb-0">
                         ${yearLevel}
                    </h4>
                </div>
        `;

        for (const semester in data[year])
        {
            const subjects = data[year][semester];

            let totalUnits = 0;

            subjects.forEach(subject => {
                totalUnits += Number(subject.subject_units);
            });

            html += `
                <div class="mb-4 bg-light bg-opacity-50 p-3 rounded-3 border">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-secondary mb-0">
                            ${semester}
                        </h6>

                        <span class="badge bg-primary">
                            Total Units: ${totalUnits}
                        </span>
                    </div>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject</th>
                                <th>Units</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
            `;

            subjects.forEach(subject => {

                html += `
                    <tr class="activity-row" >
                        <td>${subject.subject_code}</td>
                        <td>${subject.subject_name}</td>
                        <td>${subject.subject_units}</td>
                          <td>
                            <button class="btn btn-sm btn-danger del-btn" data-id="${subject.id}">
                                Remove
                            </button>
                            <td>
                                ${
                                    subject.prerequisites &&
                                    subject.prerequisites !== 'None'
                                        ? `<span class="badge bg-success">
                                            <i class="bi bi-diagram-3 me-1"></i>
                                            ${subject.prerequisites}
                                        </span>`
                                        : '<span class="text-muted">None</span>'
                                }
                            </td>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        html += `</div>`;
    }

    document.getElementById('curriculum-structure').innerHTML = html;
}

function yearParser(year)
{
    switch(+year)
    {
        case (1):
            return '1st Year'
         break;

        case (2):
            return '2nd Year'
            break;
        case (3):
            return '3rd Year'
            break;
        default:
            return '4th Year';
    }
}

  function getPdf(CurriculumID)
    {
        //  fetch(`${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`)
        // .then(response => response.blob())
        // .then(blob => {
        //     const url = URL.createObjectURL(blob);
        //     window.open(url); 
        // })
        // .catch(err => console.error(err));
        

        // take this if you dont want redirection confirmation 

        window.open(
        `${BASE_URL}/curriculum-subject/${CurriculumID}/pdf`,
        "_blank"
        );
    }

$(document).ready(function () {
    $('#subjectSelect').select2({
        placeholder: "Search subject...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#curriculumSubjectModal') 
    });

        $('#targetCurriculumSubject').select2({
        placeholder: 'Search subject...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#managePrerequisitesModal')
    });

    $('#prerequisiteSelect').select2({
        placeholder: 'Search prerequisite subjects...',
        width: '100%',
        closeOnSelect: false,
        dropdownParent: $('#managePrerequisitesModal')
    });
});


function loadPrerequisiteSubjects(curriculumId) {

    fetch(`${BASE_URL}/curriculum/subjects/${curriculumId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            return response.json();
        })
        .then(data => {

            const targetSelect = $('#targetCurriculumSubject');
            const prerequisiteSelect = $('#prerequisiteSelect');

            targetSelect.empty().append(
                new Option('Choose subject...', '', true, false)
            );

            prerequisiteSelect.empty();

            data.forEach(subject => {

                const label =
                    `${subject.subject_code} - ${subject.subject_name} ` +
                    `(Year ${subject.year_level}, ${subject.semester})`;

                targetSelect.append(
                    new Option(label, subject.curriculum_subject_id)
                );

                prerequisiteSelect.append(
                    new Option(label, subject.curriculum_subject_id)
                );
            });

            targetSelect.trigger('change');
            prerequisiteSelect.trigger('change');

        })
        .catch(error => {
            console.error('Failed to load curriculum subjects:', error);

            Swal.fire({
                title: 'Error!',
                text: 'Unable to load curriculum subjects.',
                icon: 'error'
            });
        });
}