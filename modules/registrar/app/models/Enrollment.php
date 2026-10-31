<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Enrollment extends Model
 {

    public $tableName = 'enr_enrollments';
    public $primaryKey= 'enrollment_id'; 


    protected function updatePaymentStatus($id, $status, $notes, $verifiedBy)
{
    $sql = "
        UPDATE enr_payment_submissions
        SET
            status = :status,
            notes = :notes,
            verified_at = NOW(),
            verified_by = :verified_by
        WHERE id = :id
          AND status = 'pending'
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':status' => $status,
        ':notes' => $notes,
        ':verified_by' => $verifiedBy,
        ':id' => $id
    ]);

    return $stmt->rowCount() > 0;
}


protected function viewEnrollmentInfo($id)
{
    $sql = "
        SELECT
            eps.id,
            eps.student_id,
            eps.school_year_id,
            eps.semester_id,
            eps.file_data,
            eps.file_name,
            eps.file_mime,
            eps.status,
            eps.notes,
            eps.submitted_at,
            eps.verified_at,
            eps.verified_by,

            es.student_number,
            es.year_level,
            es.course_id,

            ea.first_name,
            ea.middle_name,
            ea.surname,

            cc.name AS course_name,

            rsy.name AS school_year,
            rsem.name AS semester

        FROM enr_payment_submissions eps

        INNER JOIN enr_students es
            ON es.student_id = eps.student_id

        INNER JOIN enr_applicants ea
            ON ea.applicant_id = es.applicant_id

        LEFT JOIN rgr_courses cc
            ON cc.id = es.course_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = eps.school_year_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = eps.semester_id

        WHERE eps.id = :id
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


 protected function allEnrollmentInfo($paginate = true)
{
    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    $perPage = max(1, min($perPage, 100));
    $page = max(1, $page);

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? strtolower($_GET['order']) : 'desc';

    $order = in_array($order, ['asc', 'desc'])
        ? strtoupper($order)
        : 'DESC';

    $search = isset($_GET['search'])
        ? trim($_GET['search'])
        : '';

    $status = isset($_GET['status'])
        ? trim($_GET['status'])
        : '';

    // Base conditions
    $where = " WHERE eps.deleted_at IS NULL ";
    $params = [];

    // Search student information
    if (!empty($search)) {
        $where .= " AND (
            ea.first_name LIKE :search
            OR ea.surname LIKE :search
            OR ea.middle_name LIKE :search
            OR es.student_number LIKE :search
            OR eps.file_name LIKE :search
            OR eps.notes LIKE :search
        )";

        $params[':search'] = "%{$search}%";
    }

    // Filter payment status
    if (!empty($status)) {
        $where .= " AND eps.status = :status";
        $params[':status'] = $status;
    }

    // Count query
    $countSql = "
        SELECT COUNT(*) AS total
        FROM enr_payment_submissions eps

        LEFT JOIN enr_students es
            ON es.student_id = eps.student_id

        LEFT JOIN enr_applicants ea
            ON ea.applicant_id = es.applicant_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = eps.school_year_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = eps.semester_id

        $where
    ";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $total = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Main query
    $dataSql = "
        SELECT
            eps.id,
            eps.student_id,
            eps.school_year_id,
            eps.semester_id,

            eps.file_data,
            eps.file_name,
            eps.file_mime,

            eps.status,
            eps.notes,

            eps.submitted_at,
            eps.verified_at,
            eps.verified_by,

            es.student_number,
            es.year_level,
            es.course_id,
            rco.name AS course_name,

            ea.first_name,
            ea.middle_name,
            ea.surname,

            rsy.name AS school_year,
            rsem.name AS semester

        FROM enr_payment_submissions eps

        LEFT JOIN enr_students es
            ON es.student_id = eps.student_id

        LEFT JOIN enr_applicants ea
            ON ea.applicant_id = es.applicant_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = eps.school_year_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = eps.semester_id

        LEFT JOIN rgr_courses rco   
            ON rco.id = es.course_id
        $where

        ORDER BY eps.id $order
    ";

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset";
    }

    $dataStmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }

    if ($paginate) {
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $dataStmt->execute();

    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$paginate) {
        return $data;
    }

    return [
        'data' => $data,
        'total' => $total,
        'current_page' => $page,
        'last_page' => (int) ceil($total / $perPage)
    ];
}

    protected function allEnrolledStudentsInSchedule(int $scheduleId)
    {
 
        $sql = "
        SELECT
        e.enrollment_id,
        s.student_number,
        ea.first_name,
        ea.surname,
        e.enrollment_status,
        e.academic_standing
        FROM enr_enrollments e
        JOIN enr_students s
        ON s.student_id = e.student_id
        JOIN enr_applicants ea 
                ON ea.applicant_id = s.applicant_id
        WHERE e.schedule_id = :scheduleId
        AND e.enrollment_status = 'enrolled'
        AND e.enrollment_status != 'completed'
        ORDER BY
        s.student_number ASC;   
    ";


      

    

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':scheduleId', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }