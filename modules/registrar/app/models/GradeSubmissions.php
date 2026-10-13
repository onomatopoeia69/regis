<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class GradeSubmissions extends Model
 {
 
    public $tableName = 'rgr_grade_submissions';
    public $primaryKey = 'id';

    protected function allRegistrarGrades($paginate= true)
    {

        $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $offset = ($page - 1) * $perPage;

        $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $order = in_array($order, ['asc', 'desc']) ? strtoupper($order) : 'DESC';

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where = " WHERE 1=1 ";
        
        $params = [];

        if (!empty($search)) {
        $where .= " AND (
            description LIKE :search OR
            action LIKE :search
        )";
        $params[':search'] = "%{$search}%";
       }

    
       $countSql = "SELECT COUNT(*) as total ,
             rgs.schedule_id AS schedule_id,
            rgs.status AS submission_status,
            ccfac.first_name AS first_name,
            ccfac.last_name AS last_name,
            ccsec.section_code AS section,
            rsub.code AS subject,
            rsub.name AS subject_name
            
            FROM {$this->tableName} rgs
             JOIN cc_schedule ccs ON ccs.id = rgs.schedule_id 
            JOIN cc_sections ccsec ON ccsec.id = ccs.section_id
            JOIN cc_faculty ccfac ON ccfac.id = ccs.faculty_id
            JOIN rgr_subjects rsub ON rsub.id = ccs.subject_id
            $where
            ";

        $countStmt = $this->pdo->prepare($countSql);

        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        
        $dataSql = "SELECT 

            rgs.schedule_id AS schedule_id,
            rgs.status AS submission_status,
            ccfac.first_name AS first_name,
            ccfac.last_name AS last_name,
            ccsec.section_code AS section,
            rsub.code AS subject,
            rsub.name AS subject_name

            FROM {$this->tableName} rgs

            JOIN cc_schedule ccs ON ccs.id = rgs.schedule_id 
            JOIN cc_sections ccsec ON ccsec.id = ccs.section_id
            JOIN cc_faculty ccfac ON ccfac.id = ccs.faculty_id
            JOIN rgr_subjects rsub ON rsub.id = ccs.subject_id
            $where
            ORDER BY rgs.created_at $order";

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
            'total' => (int) $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];


    }




     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
 


 }