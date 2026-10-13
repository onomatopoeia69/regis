<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Employee;
use App\Models\GradeComponent;
use App\Models\GradeSubmissions;
use App\Models\Semester;
 use App\Models\SchoolYear;
 use App\Models\Subject;

 class GradeController extends Controller
 {

    public function index()
    {

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $sy = SchoolYear::all();
        $subject = Subject::all();
        
      
        $this->render('/students/grades',
         [
             'user' => $user,
             'semester' => $semester,
             'schoolYear' => $schoolYear,
             'sy' => $sy,
             'subject' => $subject
            ]);
 
        
    }  


    public function allRegistrarGrades()
    {
 
        $grade = GradeSubmissions::allRegistrarGrades();
        Response::json($grade);

    }     
     

 }