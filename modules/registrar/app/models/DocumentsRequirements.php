<?php 

 namespace App\Models;
 use App\Core\Model;

 use PDO;

 class DocumentsRequirements extends Model
 {

    public $tableName = 'enr_student_requirementss';
    public $primaryKey = 'id';


    protected function approveStudentRequirement($id)
    {
        $sql = "
            UPDATE $this->tableName
            SET
                status = 'verified',
                updated_at = NOW()
                WHERE id = :id
                AND status = 'pending'
                AND is_submitted = 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    protected function rejectStudentRequirement($id, $notes)
    {
        $sql = "
            UPDATE enr_student_requirementss
            SET
                status = 'rejected',
                notes = :notes,
                updated_at = NOW()
                WHERE id = :id
                AND status = 'pending'
                AND is_submitted = 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }




    protected function getStudentRequirementById($id)
{
    $sql = "
        SELECT
            esr.id,
            esr.applicant_id,
            esr.requirement_id,
            esr.file_name,
            esr.custom_name,
            esr.file_mime,
            esr.is_submitted,
            esr.submitted_date,
            esr.status,
            esr.notes,
            esr.created_at,
            esr.updated_at,

            ea.first_name,
            ea.middle_name,
            ea.surname,

            er.requirement_name AS requirement_name

        FROM $this->tableName esr

        LEFT JOIN enr_applicants ea
            ON ea.applicant_id = esr.applicant_id

        LEFT JOIN enr_requirements er
            ON er.requirement_id = esr.requirement_id

        WHERE esr.id = :id
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
}

     protected function allStudentRequirements($paginate = true)
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

        $applicantId = isset($_GET['applicant_id'])
            ? (int) $_GET['applicant_id']
            : '';

        // Base conditions
        $where = " WHERE 1=1 ";
        $params = [];

    
        if (!empty($search)) {
            $where .= " AND (
                ea.first_name LIKE :search
                OR ea.surname LIKE :search
                OR ea.middle_name LIKE :search
                OR esr.file_name LIKE :search
                OR esr.custom_name LIKE :search
                OR esr.notes LIKE :search
                OR er.requirement_name LIKE :search
            )";

            $params[':search'] = "%{$search}%";
        }

    
        if (!empty($status)) {
            $where .= " AND esr.status = :status";
            $params[':status'] = $status;
        }

        // Filter applicant
        if (!empty($applicantId)) {
            $where .= " AND esr.applicant_id = :applicant_id";
            $params[':applicant_id'] = $applicantId;
        }

        // Count query
        $countSql = "
            SELECT COUNT(*) AS total
            FROM $this->tableName esr

            LEFT JOIN enr_applicants ea
                ON ea.applicant_id = esr.applicant_id

            LEFT JOIN enr_requirements er
                ON er.requirement_id = esr.requirement_id

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
                esr.id,
                esr.applicant_id,
                esr.requirement_id,

                esr.file_data,
                esr.file_name,
                esr.custom_name,
                esr.file_mime,

                esr.is_submitted,
                esr.submitted_date,

                esr.status,
                esr.notes,

                esr.created_at,
                esr.updated_at,

                ea.first_name,
                ea.middle_name,
                ea.surname,

                er.requirement_name AS requirement_name

            FROM $this->tableName esr

            LEFT JOIN enr_applicants ea
                ON ea.applicant_id = esr.applicant_id

            LEFT JOIN enr_requirements er
                ON er.requirement_id = esr.requirement_id

            $where

            ORDER BY esr.id $order
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

   



    public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }



 }