<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helper\Response;
use App\Models\SelfEnrollment;

class SelfEnrollmentController extends Controller
{
    public function index(): void
    {
        $userId = (int) Session::get('student_user_id');
        $studentData = SelfEnrollment::getStudentData($userId);

        if (!$studentData) {
            Response::redirect('/login');
            return;
        }

        $term = SelfEnrollment::getActiveTerm();

        $this->render('/self-enrollment', [
            'name' => explode(' ', trim($studentData['full_name']))[0] ?: 'Student',
            'first_two' => substr($studentData['full_name'], 0, 2),
            'studentSchoolInfo' => [
                'student_id'     => $studentData['student_id'],
                'student_number' => $studentData['student_number'],
            ],
            'uploads' => SelfEnrollment::getUploads((int) $studentData['student_id']),
            'activeTerm' => $term,
        ]);
    }

   
public function upload(): void
{
    header('Content-Type: application/json');

    $userId = (int) Session::get('student_user_id');
    $studentData = SelfEnrollment::getStudentData($userId);

    $upload   = $_FILES['file'] ?? null;
    $notes    = trim((string) ($_POST['notes'] ?? ''));
    $fileName = trim((string) ($_POST['file_name'] ?? ''));

    if (
        !$studentData ||
        !$upload ||
        !isset($upload['error']) ||
        $upload['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($upload['tmp_name'])
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Select a valid file.'
        ]);
        return;
    }

    if ($upload['size'] > 5 * 1024 * 1024) {
        http_response_code(413);
        echo json_encode([
            'success' => false,
            'message' => 'The file must not exceed 5 MB.'
        ]);
        return;
    }

    $mimeType = (new \finfo(FILEINFO_MIME_TYPE))
        ->file($upload['tmp_name']);

    $allowedTypes = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png'
    ];

    if (!isset($allowedTypes[$mimeType])) {
        http_response_code(415);
        echo json_encode([
            'success' => false,
            'message' => 'Only PDF, JPG, and PNG files are allowed.'
        ]);
        return;
    }

    if ($fileName === '') {
        $fileName = basename($upload['name']);
    }

    $fileName = mb_substr($fileName, 0, 255);

    $hashName = bin2hex(random_bytes(32));
    $extension = $allowedTypes[$mimeType];

    $storedName = $hashName . '.' . $extension;


    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/student-documents';

    if (
        !is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true) &&
        !is_dir($uploadDir)
    ) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Unable to create upload directory.'
        ]);
        return;
    }

    $destination = $uploadDir . '/' . $storedName;

    // Relative path to save in database
    $filePath = 'uploads/student-documents/' . $storedName;

    try {
        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
            throw new \RuntimeException('Unable to move uploaded file.');
        }

        $term = SelfEnrollment::getActiveTerm();

        SelfEnrollment::saveUpload(
            (int) $studentData['student_id'],
            $filePath,
            $fileName,
            $mimeType,
            $notes !== '' ? $notes : null,
            $term['school_year_id'] ?? null,
            $term['semester_id'] ?? null
        );

        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully.'
        ]);

    } catch (\Throwable $exception) {

        // Remove uploaded file if database saving fails
        if (file_exists($destination)) {
            unlink($destination);
        }

        error_log('Student document upload failed: ' . $exception->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Upload failed. Please try again.'
        ]);
    }
}


  
public function view(int $id): void
{
    $userId = (int) Session::get('student_user_id');
    $studentData = SelfEnrollment::getStudentData($userId);

    if (!$studentData) {
        Response::redirect('/login');
        return;
    }

    $file = SelfEnrollment::getUploadFile(
        (int) $studentData['student_id'],
        $id
    );

    if (!$file) {
        http_response_code(404);
        echo 'File not found or has been archived.';
        return;
    }



    $filePath = $file['file_data'];

   $absolutePath = PUBLIC_PATH.'/'.$filePath;


    if (!is_file($absolutePath)) {
                    http_response_code(404);
                    exit('File not found.');
            }
    $mimeType = mime_content_type($absolutePath);


    $allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($mimeType, $allowedTypes, true)) {
        http_response_code(415);
        echo 'Unsupported file type.';
        return;
    }

    $safeName = preg_replace(
        '/[\r\n"]/',
        '',
        $file['file_name']
    );

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . $safeName . '"');
    header('Content-Length: ' . filesize($absolutePath));
    header('X-Content-Type-Options: nosniff');

    readfile($absolutePath);
    exit;
}


    /**
     * Archive (soft delete) — hindi nabubura sa DB.
     */
    public function delete(): void
    {
        header('Content-Type: application/json');
        $userId = (int) Session::get('student_user_id');
        $studentData = SelfEnrollment::getStudentData($userId);

        $uploadId = filter_var($_POST['upload_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$studentData || !$uploadId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        try {
            $archived = SelfEnrollment::deleteUpload((int) $studentData['student_id'], (int) $uploadId);
            if (!$archived) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'File not found or already archived.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'File archived successfully.']);
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Archive failed: ' . $exception->getMessage()]);
        }
    }

    /**
     * Restore an archived submission.
     */
    public function restore(): void
    {
        header('Content-Type: application/json');
        $userId = (int) Session::get('student_user_id');
        $studentData = SelfEnrollment::getStudentData($userId);

        $uploadId = filter_var($_POST['upload_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$studentData || !$uploadId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        try {
            $restored = SelfEnrollment::restoreUpload((int) $studentData['student_id'], (int) $uploadId);
            if (!$restored) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'File not found or not archived.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'File restored successfully.']);
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Restore failed: ' . $exception->getMessage()]);
        }
    }
}