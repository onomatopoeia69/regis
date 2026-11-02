<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;


 class TransfereeEvaluations extends Model
 {
 
    public $tableName = 'rgr_student_subject_evaluations';
    public $primaryKey = 'id';

    protected function getActiveCurriculumByCourse($courseId)
  {
      $sql = "
          SELECT
              id AS curriculum_id,
              course_id,
              curriculum_name,
              effective_year
          FROM rgr_curriculums
          WHERE course_id = :course_id
          AND is_active = 1
          LIMIT 1
      ";

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([
          ':course_id' => $courseId
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC);
  }


  protected function getApplicantTOR(int $applicantId)
{
    $sql = "
        SELECT
            esr.id AS requirement_file_id,
            esr.applicant_id,
            esr.requirement_id,
            esr.file_name,
            esr.status
        FROM enr_student_requirementss esr
        WHERE esr.applicant_id = :applicant_id
          AND esr.requirement_id = 7
        ORDER BY esr.id DESC
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->bindValue(
        ':applicant_id',
        $applicantId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}



      public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
    
 
 

 }