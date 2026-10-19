<?php 
 
  namespace App\Controllers;

  use App\Core\Controller;
  use App\Helper\Logger;
  use App\Helper\Response;
  use App\Models\Course;
  use App\Models\Employee;
  use App\Models\ExamSchedule;
  use App\Models\SchoolYear;
  use App\Models\Section;
  use App\Models\Semester;
  use Dompdf\Dompdf;

  class ExaminationScheduleController extends Controller
  {
    
    public function index()
    {
        
        $user = Employee::find('1014'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/exam_schedule', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    }


    public function getSectionOnThatDay()
    {
        $section = ExamSchedule::getSectionOnThatDay();
        Response::json($section);
    }

    public function getSchedule()
    {
      $schedule = ExamSchedule::getSchedule();
      Response::json($schedule);
    }
 
     public function examSchedulerPdf()
    {   

        $section_id = $_GET['section_id'];
        $date = $_GET['exam_date'];

        Logger::log(
        "Get A PDF of Semester Report",
         "Downloading a PDF file contains Semester Information"
         );

         $dompdf = new Dompdf();
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

        $schedule = ExamSchedule::getSchedule();
        $section = Section::find($section_id);
        
         ob_start();
        
    
        $this->render('/pdf/exam_schedule',[

            'schedules' => $schedule,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,
            'section' => $section,
            'date' => $date

            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("semester.pdf", ["Attachment" => false]);


     }

    public function imageRender($image_name)
    {

     $path = $_SERVER['DOCUMENT_ROOT'].BASE_URL."/assets/images/$image_name";
     $type = pathinfo($path, PATHINFO_EXTENSION);
     $data = file_get_contents($path);
     $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
     return $base64;

    }

  }