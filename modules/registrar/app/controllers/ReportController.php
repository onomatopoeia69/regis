<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class ReportController extends Controller 
 {

    public function index()
    {

        $user = Employee::find('1014'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $sy = SchoolYear::all();
      
        $this->render('reports/reports',
         [
            'user' => $user,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
            'sy' => $sy
        ]);

    }
   
  



 }