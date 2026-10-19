<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Course;
 use App\Models\Employee;
 use App\Models\Exam;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class ExamController extends Controller
 {
  
    
     public function index()
     {
        $user = Employee::find('1014'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/exam', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
     }


     public function allExaminations()
     {
        $exam = Exam::allExaminations();
        Response::json($exam);
     }
 

 }