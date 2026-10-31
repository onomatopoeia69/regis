<?php
 
 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Employee;
 use App\Models\Enrollment;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class EnrollmentController extends Controller 
 {

   public function index()
   {

    
        $user = Employee::find('1014');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
   
       $this->render('students/enrollment',[
            
           'user' => $user,
           'semester' => $semester,
            'schoolYear' => $schoolYear

       ]);    

   }

   public function allEnrollmentInfo()
    {
        $payments = Enrollment::allEnrollmentInfo();

        Response::json($payments);
    }

   public function allEnrolledStudentsInSchedule($id)
   {
     
     $enrollment = Enrollment::allEnrolledStudentsInSchedule($id);
     Response::json($enrollment);
    
   }

   public function approve($id)
{
  
     // replace by seesion data user
    $verifiedBy = '1014' ?? null;

    if (!$verifiedBy) {
        Response::json([
            'success' => false,
            'message' => 'Unauthorized.'
        ], 401);
        return;
    }

    $success = Enrollment::updatePaymentStatus(
        $id,
        'verified',
        null,
        $verifiedBy
    );

    if (!$success) {
        Response::json([
            'success' => false,
            'message' => 'Payment not found or already processed.'
        ], 400);
        return;
    }

    Response::json([
        'success' => true,
        'message' => 'Payment successfully approved.'
    ]);
}


    public function reject($id)
    {
    $verifiedBy = '1014' ?? null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$verifiedBy) {
        Response::json([
            'success' => false,
            'message' => 'Unauthorized.'
        ], 401);
        return;
    }

    if ($notes === '') {
        Response::json([
            'success' => false,
            'message' => 'Please provide a reason for rejection.'
        ], 422);
        return;
    }

    $success = Enrollment::updatePaymentStatus(
        $id,
        'rejected',
        $notes,
        $verifiedBy
    );

    if (!$success) {
        Response::json([
            'success' => false,
            'message' => 'Payment not found or already processed.'
        ], 400);
        return;
    }

    Response::json([
        'success' => true,
        'message' => 'Payment successfully rejected.'
    ]);
}

   public function viewEnrollmentInfo($id)
{
    $enrollment = Enrollment::viewEnrollmentInfo($id);

    if (!$enrollment) {
        Response::json([
            'success' => false,
            'message' => 'Enrollment record not found.'
        ], 404);

        return;
    }

    Response::json([
        'success' => true,
        'data' => $enrollment
    ]);
}
 


 }