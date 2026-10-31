<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SelfEnrollment
{
    public static function getStudentData(int $userId): array
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("SELECT u.user_id, u.student_id, u.full_name, s.student_number,
            a.admission_type
            FROM enr_users u
            JOIN enr_students s ON s.student_id = u.student_id
            JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            WHERE u.user_id = :user_id LIMIT 1");
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Kunin ang active school year + semester IDs.
     */
    public static function getActiveTerm(): array
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare(
                "SELECT
                    (SELECT id FROM rgr_school_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1) AS sy_id,
                    (SELECT id FROM rgr_semesters    WHERE is_active = 1 ORDER BY id DESC LIMIT 1) AS sem_id"
            );
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'school_year_id' => (int) ($row['sy_id']  ?? 0),
                'semester_id'    => (int) ($row['sem_id'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('SelfEnrollment::getActiveTerm error: ' . $e->getMessage());
            return ['school_year_id' => 0, 'semester_id' => 0];
        }
    }

    /**
     * Kunin ang lahat ng ACTIVE (hindi archived) payment submissions ng student.
     */
    public static function getUploads(int $studentId): array
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("SELECT id, student_id, school_year_id, semester_id,
                file_name, file_mime, status, notes, submitted_at, verified_at, verified_by
            FROM enr_payment_submissions
            WHERE student_id = :student_id
              AND deleted_at IS NULL
              AND file_data IS NOT NULL
              AND file_name IS NOT NULL
              AND file_name <> ''
            ORDER BY submitted_at DESC, id DESC");
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kunin ang ARCHIVED submissions (optional — para sa audit/admin view).
     */
    public static function getArchivedUploads(int $studentId): array
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("SELECT id, student_id, school_year_id, semester_id,
                file_name, file_mime, status, notes, submitted_at, verified_at, verified_by, deleted_at
            FROM enr_payment_submissions
            WHERE student_id = :student_id
              AND deleted_at IS NOT NULL
            ORDER BY deleted_at DESC, id DESC");
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function saveUpload(
        int $studentId,
        string $fileData,
        string $fileName,
        string $mimeType,
        ?string $notes,
        ?int $schoolYearId = null,
        ?int $semesterId = null
    ): void {
        $pdo = Database::connect();

        // Kung walang SY/sem naipasa, kusang kunin ang active
        if ($schoolYearId === null || $semesterId === null) {
            $term = self::getActiveTerm();
            $schoolYearId = $schoolYearId ?? ($term['school_year_id'] ?: null);
            $semesterId   = $semesterId   ?? ($term['semester_id']    ?: null);
        }

        // Check existing ACTIVE pending for same SY/sem (exclude archived)
        $statement = $pdo->prepare("SELECT id FROM enr_payment_submissions
            WHERE student_id = :student_id
              AND COALESCE(school_year_id, 0) = COALESCE(:school_year_id, 0)
              AND COALESCE(semester_id, 0) = COALESCE(:semester_id, 0)
              AND status = 'pending'
              AND deleted_at IS NULL
            LIMIT 1");
        $statement->execute([
            'student_id'     => $studentId,
            'school_year_id' => $schoolYearId,
            'semester_id'    => $semesterId,
        ]);
        $existing = $statement->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $statement = $pdo->prepare("UPDATE enr_payment_submissions
                SET file_data = :file_data,
                    file_name = :file_name,
                    file_mime = :file_mime,
                    notes = :notes,
                    submitted_at = NOW(),
                    status = 'pending'
                WHERE id = :id");
            $statement->execute([
                'file_data' => $fileData,
                'file_name' => $fileName,
                'file_mime' => $mimeType,
                'notes'     => $notes,
                'id'        => $existing['id'],
            ]);
            return;
        }

        $statement = $pdo->prepare("INSERT INTO enr_payment_submissions
            (student_id, school_year_id, semester_id, file_data, file_name, file_mime,
             status, notes, submitted_at)
            VALUES (:student_id, :school_year_id, :semester_id, :file_data, :file_name,
                    :file_mime, 'pending', :notes, NOW())");
        $statement->execute([
            'student_id'     => $studentId,
            'school_year_id' => $schoolYearId,
            'semester_id'    => $semesterId,
            'file_data'      => $fileData,
            'file_name'      => $fileName,
            'file_mime'      => $mimeType,
            'notes'          => $notes,
        ]);
    }

    /**
     * Kunin ang file — hindi kasama ang archived.
     */
    public static function getUploadFile(int $uploadId): array|false
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("SELECT id, file_data, file_name, file_mime
            FROM enr_payment_submissions
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1");
        $statement->execute([
            'id'         => $uploadId,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * SOFT DELETE — i-archive lang, hindi tinatanggal sa DB.
     */
    public static function deleteUpload(int $studentId, int $uploadId): bool
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("UPDATE enr_payment_submissions
            SET deleted_at = NOW()
            WHERE id = :id
              AND student_id = :student_id
              AND deleted_at IS NULL");
        $statement->execute([
            'id'         => $uploadId,
            'student_id' => $studentId,
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * RESTORE — ibalik ang archived file.
     */
    public static function restoreUpload(int $studentId, int $uploadId): bool
    {
        $pdo = Database::connect();
        $statement = $pdo->prepare("UPDATE enr_payment_submissions
            SET deleted_at = NULL
            WHERE id = :id
              AND student_id = :student_id
              AND deleted_at IS NOT NULL");
        $statement->execute([
            'id'         => $uploadId,
            'student_id' => $studentId,
        ]);

        return $statement->rowCount() > 0;
    }
}