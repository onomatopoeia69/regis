<?php 

   namespace App\Controllers;

   use App\Core\Controller;
   use App\Helper\Response;
   use App\Models\DocumentsRequirements;
   use App\Models\Employee;
   use App\Models\EnrollmentNotification;
   use App\Models\SchoolYear;
   use App\Models\Semester;
   use Throwable;

   class DocumentRequirementsController extends Controller
   {
   
    public function index()
    {

        $user = Employee::find('1014');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/students/applicant_documents', 
        [    
        'user' => $user,
        'semester' => $semester,
        'schoolYear' => $schoolYear
      ]);

    }

    public function allStudentRequirements()
    {
 
        $req = DocumentsRequirements::allStudentRequirements();
        Response::json($req);
 
    }

    public function getStudentRequirementById($id)
    {

    

        $requirement = DocumentsRequirements::getStudentRequirementById($id);

        if (!$requirement) {
            Response::json([
                'success' => false,
                'message' => 'Requirement not found.'
            ]);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $requirement
        ]);

    }

    public function approveRequirement($id)
    {
    try {

       
        $requirement = DocumentsRequirements::getStudentRequirementById($id);

        if (!$requirement) {
            Response::json([
                'success' => false,
                'message' => 'Requirement not found.'
            ]);
            return;
        }

        if ($requirement['status'] !== 'pending') {
            Response::json([
                'success' => false,
                'message' => 'This requirement has already been processed.'
            ]);
            return;
        }


        $success = DocumentsRequirements::approveStudentRequirement($id);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Unable to approve requirement.'
            ]);
            return;
        }

        EnrollmentNotification::create([
            'title' => 'Requirement Approval',
            'message' => 'The submitted requirement has been successfully reviewed and approved.',
            'type' => 'enrollment',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Response::json([
            'success' => true,
            'message' => 'Requirement successfully verified.'
        ]);

    } catch (Throwable $e) {

        error_log($e->getMessage());

        Response::json([
            'success' => false,
            'message' => 'An error occurred while approving the requirement.'
        ]);
    }
    }




    public function rejectRequirement($id)
{
    try {

    
        $notes = trim($_POST['notes'] ?? '');

        if (empty($notes)) {
            Response::json([
                'success' => false,
                'message' => 'Please provide a reason for rejection.'
            ]);
            return;
        }


        $requirement = DocumentsRequirements::getStudentRequirementById($id);

        if (!$requirement) {
            Response::json([
                'success' => false,
                'message' => 'Requirement not found.'
            ]);
            return;
        }

    
        if ($requirement['status'] !== 'pending') {
            Response::json([
                'success' => false,
                'message' => 'This requirement has already been processed.'
            ]);
            return;
        }

       

        $success = DocumentsRequirements::rejectStudentRequirement($id, $notes);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Unable to reject requirement.'
            ]);
            return;
        }

         EnrollmentNotification::create([
            'title' => 'Requirement Approval',
            'message' => 'The requirement has been rejected. The applicant must review the remarks and submit the corrected document.',
            'type' => 'enrollment',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Response::json([
            'success' => true,
            'message' => 'Requirement successfully rejected.'
        ]);

    } catch (Throwable $e) {

        error_log($e->getMessage());

        Response::json([
            'success' => false,
            'message' => 'An error occurred while rejecting the requirement.'
        ]);
    }   
    }

 
     
   }