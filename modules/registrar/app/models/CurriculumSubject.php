<?php 
  
  namespace App\Models;

  use App\Core\Model;
  use PDO;

  class CurriculumSubject extends Model 
  {

    public $tableName = 'rgr_curriculum_subjects';
    public $primaryKey = 'id';


     protected function allCurriculumSubjectsUsingId($id,$paginate=false)
    {
 

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = "";
    $params = [];



if (!empty($search)) {
    $where .= " WHERE (
        rc.name LIKE :search
        OR rs.name LIKE :search
    )";

    $params[':search'] = "%{$search}%";
}


 $countSql = "
SELECT COUNT(*) as total

FROM {$this->tableName} cusu

JOIN rgr_curriculums rcu 
    ON rcu.id = cusu.curriculum_id

JOIN rgr_courses rc 
    ON rc.id = rcu.course_id

JOIN enr_students ens 
    ON ens.course_id = rc.id

JOIN enr_enrollments enr 
    ON enr.student_id = ens.student_id

JOIN cc_schedule ccs 
    ON ccs.id = enr.schedule_id
    AND ccs.subject_id = cusu.subject_id

JOIN rgr_subjects rs 
    ON rs.id = cusu.subject_id

$where
";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

$dataSql = "
SELECT
    cusu.id AS id,
    cusu.year_level,
    cusu.semester,
    rcu.curriculum_name,
    rcu.effective_year,
    rc.code AS course_code,
    rc.name AS course_name,
    rs.code AS subject_code,
    rs.name AS subject_name,
    rs.units AS subject_units,
    MAX(enr_status.status) AS status

FROM {$this->tableName} cusu

JOIN rgr_curriculums rcu
    ON rcu.id = cusu.curriculum_id

JOIN rgr_courses rc
    ON rc.id = rcu.course_id

JOIN enr_students ens
    ON ens.course_id = rc.id
    AND ens.student_id = :student_id

JOIN rgr_subjects rs
    ON rs.id = cusu.subject_id

LEFT JOIN (
    SELECT 
        ccs.subject_id,
        ccs.status
    FROM enr_enrollments enr
    JOIN cc_schedule ccs ON ccs.id = enr.schedule_id
    JOIN rgr_semesters rsem ON rsem.id = ccs.semester_id
    JOIN rgr_school_years rsy ON rsy.id = rsem.school_year_id
    WHERE enr.student_id = :student_id
      AND rsy.is_active = 1
) enr_status ON enr_status.subject_id = cusu.subject_id

$where

GROUP BY
    cusu.id,
    cusu.year_level,
    cusu.semester,
    rcu.curriculum_name,
    rcu.effective_year,
    rc.code,
    rc.name,
    rs.code,
    rs.name,
    rs.units

ORDER BY
    cusu.year_level ASC,
    cusu.semester ASC,
    rs.code ASC
";

$dataStmt = $this->pdo->prepare($dataSql);

$dataStmt->bindValue(':student_id', $id, PDO::PARAM_INT);

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


    

   

    protected function allCurriculumSubjects($id,$paginate=false)
    {
 
    $curriculum_id = $id ?? '';

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = " WHERE 1 = 1";
    $params = [];


    if (!empty($search)) {
        $where .= " AND (
            course_name LIKE :search
            OR subject_name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($curriculum_id)) {
    $where .= " AND cusu.curriculum_id = :curriculum_id";
    $params[':curriculum_id'] = $curriculum_id;
    }

  
    $countSql = "SELECT COUNT(*) as total 
                 FROM {$this->tableName} cusu
                 JOIN rgr_curriculums rcu ON rcu.id = cusu.curriculum_id
     JOIN rgr_courses rc ON rc.id = rcu.course_id
     JOIN rgr_subjects rs ON rs.id = cusu.subject_id
                 $where";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    

$dataSql = "SELECT
        cusu.id AS id,
        cusu.year_level,
        cusu.semester,
        rcu.curriculum_name,
        rcu.effective_year,
        rc.code AS course_code,
        rc.name AS course_name,
        rs.code AS subject_code,
        rs.name AS subject_name,
        rs.units AS subject_units,

        COALESCE(
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    prereq_subject.code,
                    ' - ',
                    prereq_subject.name
                )
                ORDER BY prereq_subject.code
                SEPARATOR ', '
            ),
            'None'
        ) AS prerequisites

    FROM {$this->tableName} cusu

    JOIN rgr_curriculums rcu
        ON rcu.id = cusu.curriculum_id

    JOIN rgr_courses rc
        ON rc.id = rcu.course_id

    JOIN rgr_subjects rs
        ON rs.id = cusu.subject_id

    LEFT JOIN rgr_curriculum_prerequisites cp
        ON cp.curriculum_subject_id = cusu.id

    LEFT JOIN rgr_curriculum_subjects prereq
        ON prereq.id = cp.prerequisite_curriculum_subject_id

    LEFT JOIN rgr_subjects prereq_subject
        ON prereq_subject.id = prereq.subject_id

    $where

    GROUP BY
        cusu.id,
        cusu.year_level,
        cusu.semester,
        rcu.curriculum_name,
        rcu.effective_year,
        rc.code,
        rc.name,
        rs.code,
        rs.name,
        rs.units

    ORDER BY
        cusu.year_level ASC,
        cusu.semester ASC,
        rs.code ASC
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
        'total' => (int) $total,
        'current_page' => $page,
        'last_page' => ceil($total / $perPage)
    ];



    }


    protected function getAllCurriculumSubjects($id)
    {

        $sql = "SELECT
            cs.id AS curriculum_subject_id,
            cs.curriculum_id,
            cs.subject_id,
            s.code AS subject_code,
            s.name AS subject_name,
            s.units,
            cs.year_level,
            cs.semester
        FROM rgr_curriculum_subjects cs
        INNER JOIN rgr_subjects s
            ON s.id = cs.subject_id
        WHERE cs.curriculum_id = :curriculum_id
        ORDER BY
            cs.year_level ASC,
            FIELD(cs.semester, '1st Semester', '2nd Semester', 'Summer'),
            s.code ASC; ";

         $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':curriculum_id' => $id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);


    }


    protected function savePrerequisites($curriculum_subject_id, array $prerequisite_ids)
{
    $pdo = $this->pdo;

    $targetId = (int) $curriculum_subject_id;

    // Remove empty values, convert IDs to integers, and remove duplicates.
    $prerequisite_ids = array_values(array_unique(array_filter(
        array_map('intval', $prerequisite_ids),
        fn($id) => $id > 0
    )));

    if ($targetId <= 0) {
        throw new Exception('Please select a valid subject.');
    }

    if (in_array($targetId, $prerequisite_ids, true)) {
        throw new Exception('A subject cannot be its own prerequisite.');
    }

    try {
        $pdo->beginTransaction();

        // Confirm that the target subject exists.
        $stmt = $pdo->prepare("
            SELECT id, curriculum_id
            FROM rgr_curriculum_subjects
            WHERE id = ?
        ");
        $stmt->execute([$targetId]);

        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            throw new Exception('Selected curriculum subject was not found.');
        }

        // Confirm all prerequisites belong to the same curriculum.
        if (!empty($prerequisite_ids)) {

            $placeholders = implode(',', array_fill(
                0,
                count($prerequisite_ids),
                '?'
            ));

            $stmt = $pdo->prepare("
                SELECT id
                FROM rgr_curriculum_subjects
                WHERE curriculum_id = ?
                AND id IN ($placeholders)
            ");

            $stmt->execute(array_merge(
                [(int) $target['curriculum_id']],
                $prerequisite_ids
            ));

            $validIds = array_map(
                'intval',
                $stmt->fetchAll(PDO::FETCH_COLUMN)
            );

            if (count($validIds) !== count($prerequisite_ids)) {
                throw new Exception(
                    'All prerequisite subjects must belong to the same curriculum.'
                );
            }
        }

        // Replace the previous prerequisites for this subject.
        $stmt = $pdo->prepare("
            DELETE FROM rgr_curriculum_prerequisites
            WHERE curriculum_subject_id = ?
        ");
        $stmt->execute([$targetId]);

        // Insert the newly selected prerequisites.
        if (!empty($prerequisite_ids)) {

            $stmt = $pdo->prepare("
                INSERT INTO rgr_curriculum_prerequisites
                (
                    curriculum_subject_id,
                    prerequisite_curriculum_subject_id
                )
                VALUES (?, ?)
            ");

            foreach ($prerequisite_ids as $prerequisiteId) {
                $stmt->execute([
                    $targetId,
                    $prerequisiteId
                ]);
            }
        }

        $pdo->commit();

        return true;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

    
    public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }
 
 

  }