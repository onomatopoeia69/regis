<?php 

namespace App\Models;

use App\Core\Model;
use PDO;


class Transfer extends Model
{   

    
protected function allTransfereesWithTor($paginate = true)
{
    // Pagination
    $limit = max(1, min((int) ($_GET['limit'] ?? 10), 100));
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['search'] ?? '');
    $order = strtoupper($_GET['order'] ?? 'DESC');
    $order = $order === 'ASC' ? 'ASC' : 'DESC';

    // Base query
   $sql = "
    FROM enr_applicants a

    INNER JOIN rgr_courses rcs 
        ON rcs.id = a.course_id

    INNER JOIN enr_student_requirementss r
        ON r.applicant_id = a.applicant_id

    INNER JOIN enr_requirements req
        ON req.requirement_id = r.requirement_id

    WHERE a.admission_type = 'transferee'
    AND req.requirement_name = 'TOR'
    AND req.requirement_category = 'transferee'
";

    $params = [];

    // Search
    if ($search !== '') {
        $sql .= "
            AND (
                a.first_name LIKE :search
                OR a.surname LIKE :search
                OR a.school_last_attended LIKE :search
                OR a.email LIKE :search
            )
        ";

        $params['search'] = "%{$search}%";
    }

    // Count records
    $countSql = "
        SELECT COUNT(DISTINCT a.applicant_id) AS total
        $sql
    ";

    $countStmt = $this->pdo->prepare($countSql);
    $countStmt->execute($params);

    $total = (int) $countStmt->fetchColumn();

    // Retrieve records
    $dataSql = "
        SELECT
            a.applicant_id,
            CONCAT(a.first_name, ' ', a.surname) AS student_name,
            a.first_name,
            a.surname,
            a.email,
            a.contact_number,
            a.school_last_attended,
            rcs.code AS course_name,
            

            req.requirement_name,

            r.id AS tor_file_id,
            r.file_name,
            r.custom_name,
            r.file_mime,
            r.is_submitted,
            r.status AS tor_status,
            r.submitted_date

        $sql

        ORDER BY r.submitted_date $order
    ";

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }

    if ($paginate) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return paginated or complete records
    if (!$paginate) {
        return $data;
    }

    return [
        'data' => $data,
        'total' => $total,
        'current_page' => $page,
        'last_page' => max(1, (int) ceil($total / $limit)),
        'summary' => $this->transfereeSummary()
    ];
}



protected function transfereeSummary()
{
    $sql = "
        SELECT
            /* No subject evaluation records yet */
            COUNT(DISTINCT CASE
                WHEN ev.applicant_id IS NULL
                THEN a.applicant_id
            END) AS pending,

            /* At least one subject still needs evaluation */
            COUNT(DISTINCT CASE
                WHEN ev.pending_evaluations > 0
                THEN a.applicant_id
            END) AS for_evaluation,

            /* All subjects reviewed, but no credited subjects */
            COUNT(DISTINCT CASE
                WHEN ev.total_evaluations > 0
                AND ev.pending_evaluations = 0
                AND COALESCE(cr.total_credits, 0) = 0
                THEN a.applicant_id
            END) AS evaluated,

            /* At least one officially credited subject */
            COUNT(DISTINCT CASE
                WHEN cr.total_credits > 0
                THEN a.applicant_id
            END) AS credited

        FROM enr_applicants a

        INNER JOIN enr_student_requirementss r
            ON r.applicant_id = a.applicant_id

        INNER JOIN enr_requirements req
            ON req.requirement_id = r.requirement_id

        /* Evaluation records linked using applicant_id */
        LEFT JOIN (
            SELECT
                applicant_id,
                COUNT(*) AS total_evaluations,
                SUM(status = 'pending') AS pending_evaluations
            FROM rgr_student_subject_evaluations
            WHERE applicant_id IS NOT NULL
            GROUP BY applicant_id
        ) ev
            ON ev.applicant_id = a.applicant_id

        /* Credits linked through evaluation_id */
        LEFT JOIN (
            SELECT
                e.applicant_id,
                COUNT(c.id) AS total_credits
            FROM rgr_student_credited_subjects c
            INNER JOIN rgr_student_subject_evaluations e
                ON e.id = c.evaluation_id
            GROUP BY e.applicant_id
        ) cr
            ON cr.applicant_id = a.applicant_id

        WHERE a.admission_type = 'transferee'
        AND req.requirement_name = 'TOR'
        AND req.requirement_category = 'transferee'
        AND r.is_submitted = 1
        AND r.status = 'verified'
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}




    
     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
    

}