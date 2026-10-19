<?php 

  namespace App\Controllers;

  use App\Core\Controller;
  use App\Helper\Response;
  use App\Models\Course;
  use App\Models\Employee;
  use App\Models\Exam;
  use App\Models\SchoolYear;
  use App\Models\Semester;

  class ExaminationsController extends Controller
  {

    public function index()
    {
        
        $user = Employee::find('1014'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/examinations', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    }

    public function allExaminationsSplittedByTime(int $id)
    {
      $exam = Exam::allExaminationsSplittedByTime($id);
      Response::json($exam);
     
    }
   
 
      
  }