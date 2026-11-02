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



protected function getApplicantEvaluatedSubjects($applicantId)
{
    $sql = "
        SELECT
            e.id AS evaluation_id,
            e.applicant_id,
            e.requirement_file_id,

            e.previous_subject_code,
            e.previous_subject_name,
            e.previous_grade,
            e.previous_units,

            e.curriculum_subject_id,
            e.status,
            e.remarks,
            e.evaluated_at,

            rs.name AS curriculum_subject_name,
            rs.code AS curriculum_subject_code,
            rs.units AS curriculum_units

        FROM rgr_student_subject_evaluations e

        LEFT JOIN rgr_curriculum_subjects cs
            ON cs.id = e.curriculum_subject_id
        
        LEFT JOIN rgr_subjects rs 
            ON rs.id = cs.subject_id

        WHERE e.applicant_id = :applicant_id

        ORDER BY e.id ASC
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':applicant_id' => $applicantId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



      public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
    
 
 

 }