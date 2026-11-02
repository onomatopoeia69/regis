<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Applicants;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Transfer;
use App\Models\TransfereeEvaluations;
use Throwable;

 class TransfereeController extends Controller
 {
  
    public function index()
    {
 
        $user = Employee::find('1014');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/transferee/transferee_applications',
         ['user' => $user,
                 'semester' => $semester,
                 'schoolYear' => $schoolYear
             ]);


    }

   public function getApplications()
{
    try {

        $applications = Transfer::allTransfereesWithTor();

        Response::json([
            'success' => true,
            'message' => 'Transferee applications retrieved successfully.',

            'data' => $applications['data'],
            'total' => $applications['total'],
            'current_page' => $applications['current_page'],
            'last_page' => $applications['last_page'],

            // Summary cards
            'summary' => $applications['summary']
        ]);

    } catch (Throwable $e) {

        error_log("TRANSFEREE ERROR: " . $e->getMessage());

        Response::json([
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}


    public function evaluation($id)
    {
        $applicant = Applicants::find($id);
          $user = Employee::find('1014');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $curriculum = TransfereeEvaluations::getActiveCurriculumByCourse($applicant['course_id']);
        $requirement_file = TransfereeEvaluations::getApplicantTOR($applicant['applicant_id']);
        

        $this->render('/transferee/transferee_evaluation', [
            'user' => $user,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
            'applicant' => $applicant,
            'curriculum' => $curriculum,
            'requirement_file' => $requirement_file
        ]);
    }


    public function store()
    {   
    try {

       
        $applicantId = (int)($_POST['applicant_id'] ?? 0);
        $curriculumSubjectId = (int)($_POST['curriculum_subject_id'] ?? 0);
        $requirement_file_id = (int)($_POST['requirement_file_id'] ?? 0);
        $previousSubjectCode = trim($_POST['previous_subject_code'] ?? '');
        $previousSubjectName = trim($_POST['previous_subject_name'] ?? '');
        $previousGrade = $_POST['previous_grade'] ?? null;
        $previousUnits = $_POST['previous_units'] ?? null;
        $remarks = trim($_POST['remarks'] ?? '');

        
        if (
            $applicantId <= 0 ||
            $requirement_file_id === '' ||
            $curriculumSubjectId <= 0 ||
            $previousSubjectName === '' ||
            $previousGrade === null ||
            $previousGrade === '' ||
            $previousUnits === null ||
            $previousUnits === ''
        ) {
            Response::json([
                'success' => false,
                'message' => 'Please complete all required fields.'
            ]);
            return;
        }

     
        if (!is_numeric($previousGrade) || !is_numeric($previousUnits)) {
            Response::json([
                'success' => false,
                'message' => 'Grade and units must be valid numbers.'
            ]);
            return;
        }

        if ((float)$previousUnits < 0) {
            Response::json([
                'success' => false,
                'message' => 'Units cannot be negative.'
            ]);
            return;
        }


        $saved = TransfereeEvaluations::create([
            'applicant_id' => $applicantId,
            'requirement_file_id' => $requirement_file_id,
            'curriculum_subject_id' => $curriculumSubjectId,
            'previous_subject_code' => $previousSubjectCode,
            'previous_subject_name' => $previousSubjectName,
            'previous_grade' => (float)$previousGrade,
            'previous_units' => (float)$previousUnits,
            'remarks' => $remarks,
            'status' => 'pending'
        ]);

        if (!$saved) {
            Response::json([
                'success' => false,
                'message' => 'Unable to save the evaluation.'
            ]);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Previous subject evaluation saved successfully.'
        ]);

    } catch (\Throwable $e) {

        error_log('Evaluation Store Error: ' . $e->getMessage());

       

            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return;
    }
}

    public function getApplicantEvaluatedSubjects($id)
    {
        $subjects = TransfereeEvaluations::getApplicantEvaluatedSubjects($id);

        Response::json($subjects);
    }

    public function deleteEvaluation($id)
{
    try {

        $deleted = TransfereeEvaluations::delete($id);

        if (!$deleted) {
            Response::json([
                'success' => false,
                'message' => 'Evaluation not found or cannot be deleted.'
            ]);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Subject evaluation deleted successfully.'
        ]);

    } catch (Throwable $e) {

        error_log($e->getMessage());

        Response::json([
            'success' => false,
            'message' => 'An error occurred while deleting the evaluation.'
        ]);
    }
}



 
 

 }