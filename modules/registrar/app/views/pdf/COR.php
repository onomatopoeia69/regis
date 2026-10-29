<?php
/**
 * Certificate of Registration (COR) template closely following the supplied reference.
 * Faculty/instructor column removed. Delivery mode is shown as FACE-TO-FACE or ONLINE.
 * Expected variables: $enrollments, $school_image (optional), $ched_image (optional).
 */
$enrollments = is_array($enrollments ?? null) ? $enrollments : [];
$info = $enrollments[0] ?? null;

if (!function_exists('cor_e')) {
    function cor_e($value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('cor_time')) {
    function cor_time($time): string
    {
        if (empty($time)) return '';
        $timestamp = strtotime((string)$time);
        return $timestamp === false ? '' : date('g:i A', $timestamp);
    }
}
if (!function_exists('cor_is_online')) {
    function cor_is_online(array $subject): bool
    {
        foreach (['modality', 'learning_mode', 'delivery_mode', 'class_mode', 'schedule_type', 'mode_of_learning', 'meeting_type', 'instruction_mode'] as $key) {
            $value = strtolower(trim((string)($subject[$key] ?? '')));
            if ($value !== '' && (strpos($value, 'online') !== false || strpos($value, 'virtual') !== false || strpos($value, 'remote') !== false || strpos($value, 'e-learning') !== false)) {
                return true;
            }
        }
        return false;
    }
}

$studentName = trim((string)($info['student_last_name'] ?? '') . ' ' . (string)($info['student_first_name'] . ' ' .(string)($info['student_middle_name'])  ?? ''));
$totalSubjects = count($enrollments);
$totalUnits = 0;
foreach ($enrollments as $subject) {
    $totalUnits += (float)($subject['units'] ?? 3);
}
$schoolName = 'Bestlink College of the Philippines - Bulacan Inc.';
$schoolAddress = 'Lot 1 Ipo Road, Minuyan Proper, City of San Jose Del Monte, Bulacan';
$schoolPhone = 'Tel: (044) 797-2949';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate of Registration</title>
<style>
    @page { size: A4 portrait; margin: 12mm 12mm 10mm 12mm; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 7px; line-height: 1.12; }
    .page { width: 100%; }
    .school-head { width: 100%; border-collapse: collapse; margin: 0 0 2px 0; }
    .school-head td { border: 0; padding: 0; text-align: center; vertical-align: middle; }
    .school-head .logo-cell { width: 12%; text-align: center; }
    .school-head .heading-cell { width: 88%; text-align: center; }
    .crest { height: 30px; max-width: 42px; }
    .school-name { font-weight: bold; font-size: 8px; line-height: 1.1; }
    .school-address { font-size: 5.8px; line-height: 1.15; }
    .school-phone { font-size: 5.6px; }
    .document-title { text-align: center; font-weight: bold; font-size: 8px; letter-spacing: 1px; margin: 5px 0 9px; }
    .student-info { width: 100%; border-collapse: collapse; margin: 0 0 7px; table-layout: fixed; }
    .student-info td { border: 0; padding: 1.4px 2px; vertical-align: top; font-size: 6.2px; }
    .student-info .label { font-weight: bold; width: 14%; }
    .student-info .value { width: 36%; }
    .student-info .right-label { font-weight: bold; width: 15%; }
    .student-info .right-value { width: 35%; }
    .subject-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
    .subject-table th, .subject-table td { border: 0.55px solid #555; padding: 2px 1.5px; font-size: 5.45px; line-height: 1.08; vertical-align: middle; overflow-wrap: break-word; }
    .subject-table th { font-weight: bold; text-align: center; font-size: 5.15px; }
    .subject-table .group-heading { font-size: 5.2px; letter-spacing: .1px; }
    .subject-table .online-cell { text-align: center; }
    .mode { font-size: 5.2px; text-align: center; }
    .center { text-align: center; }
    .right { text-align: right; }
    .totals { width: 100%; border-collapse: collapse; margin: 1px 0 7px; }
    .totals td { border: 0; padding: 2px 3px; font-size: 6px; }
    .financial { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 1px; }
    .financial > tbody > tr > td { border: 0.7px solid #555; padding: 4px; vertical-align: top; height: 185px; }
    .particulars-title { font-weight: bold; font-size: 5.8px; margin: 0 0 3px; padding: 0 0 2px; }
    .assessment { width: 100%; border-collapse: collapse; }
    .assessment td { border: 0; padding: 1.1px 1px; font-size: 5.5px; vertical-align: top; }
    .assessment .amount { text-align: right; white-space: nowrap; width: 28%; }
    .assessment .section-label td { padding-top: 4px; font-weight: bold; }
    .assessment .total td { border-top: 0.5px solid #888; font-weight: bold; padding-top: 3px; }
    .rules { font-size: 6.2px; line-height: 1.32; text-align: center; padding: 48px 8px 0; }
    .signature-table { width: 100%; border-collapse: collapse; margin-top: 13px; table-layout: fixed; }
    .signature-table td { border: 0; width: 50%; padding: 0 12px; vertical-align: bottom; text-align: center; font-size: 5.8px; }
    .signature-line { height: 19px; border-bottom: 0.6px solid #555; margin: 0 auto 3px; width: 58%; }
    .signature-caption { font-size: 5.5px; }
    .note { font-size: 5.3px; text-align: center; margin-top: 8px; line-height: 1.25; }
    .generated { font-size: 5.1px; text-align: center; margin-top: 4px; }
</style>
</head>
<body>
<div class="page">
    <table width="100%" style="border-collapse: collapse;">
    <tr>
        <!-- BCP logo: closer to heading -->
        <td width="20%" align="right" valign="middle"
            style="padding: 0 3px 0 0;">
            <?php if (!empty($school_image)): ?>
                <img src="<?= $school_image ?>"
                     height="45"
                     alt="BCP Logo">
            <?php endif; ?>
        </td>

        <!-- School heading -->
        <td width="60%" align="center" valign="middle"
            style="padding: 0 0 0 3px; line-height: 1.2;">
            <strong style="font-size: 13px;">
                Bestlink College of the Philippines
            </strong><br>
            <strong style="font-size: 12px;">
                Bulacan Inc.
            </strong><br>
            <span style="font-size: 8px;">
                Lot 1 Ipo Road, Barangay Minuyan Proper,
                City of San Jose Del Monte, Bulacan
            </span>
        </td>

        <!-- CHED logo -->
        <td width="20%" align="left" valign="middle"
            style="padding: 0 0 0 3px;">
            <?php if (!empty($ched_image)): ?>
                <img src="<?= $ched_image ?>"
                     height="45"
                     alt="CHED Logo">
            <?php endif; ?>
        </td>
    </tr>
</table>
    <div class="document-title">C E R T I F I C A T E&nbsp; O F&nbsp; R E G I S T R A T I O N</div>

    <?php if ($info): ?>
    <table class="student-info">
        <tr>
            <td class="label">Student Number:</td><td class="value" style="color: red;"><?= cor_e($info['student_number'] ?? '') ?></td>
            <td class="right-label">Academic Year:</td><td class="right-value" style="color: red;"><?= cor_e($info['school_year_name'] ?? '') ?>, <?= cor_e($info['semester_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Registration Date:</td><td class="value"><?= cor_e($info['registration_date'] ?? '') ?></td>
            <td class="right-label">Department:</td><td class="right-value"><?= cor_e($info['department'] ?? 'College Computer Studies') ?></td>
        </tr>
        <tr>
            <td class="label">Student Name:</td><td class="value" style="color: black; font-weight: 700;"><?= cor_e($studentName) ?></td>
            <td class="right-label">Program:</td><td class="right-value"><?= cor_e($info['course_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Year Level:</td><td class="value"><?= cor_e($info['year_level'] ?? 1) . (
                    ($info['year_level'] ?? 1) == 1 ? 'st' :
                    (($info['year_level'] ?? 1) == 2 ? 'nd' :
                    (($info['year_level'] ?? 1) == 3 ? 'rd' : 'th'))) ?> Year
            </td>
            <td class="right-label">Major:</td><td class="right-value"><?= cor_e($info['major'] ?? '-') ?></td>
        </tr>
    </table>

    <table class="subject-table">
        <colgroup>
            <col style="width:8%">
            <col style="width:24%">
            <col style="width:6%">
            <col style="width:14%">
            <col style="width:5%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:19%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">CODE</th>
                <th rowspan="2">SUBJECT TITLE</th>
                <th rowspan="2">CREDITS</th>
                <th rowspan="2">SECTION</th>
                <th class="group-heading" colspan="3">FACE TO FACE</th>
                <th class="group-heading" colspan="3">ONLINE</th>
            </tr>
            <tr>
                <th>DAY</th><th>TIME</th><th>ROOM</th>
                <th>DAY</th><th>TIME</th><th>ROOM</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($enrollments as $subject):
            $units = (float)($subject['units'] ?? 3);
            $online = cor_is_online($subject);
            $actualDay = trim((string)($subject['day_of_week'] ?? ''));
            $startTime = cor_time($subject['start_time'] ?? '');
            $endTime = cor_time($subject['end_time'] ?? '');
            $actualTime = ($startTime !== '' && $endTime !== '') ? $startTime . ' - ' . $endTime : '-';
            $actualRoom = trim((string)($subject['room'] ?? ''));
            if ($online) {
                $faceDay = '-'; $faceTime = '-'; $faceRoom = '-';
                $onlineDay = '-'; $onlineTime = '-'; $onlineRoom = 'HOME';
            } else {
                $faceDay = $actualDay !== '' ? $actualDay : '-';
                $faceTime = $actualTime;
                $faceRoom = $actualRoom !== '' ? $actualRoom : '-';
                $onlineDay = '-'; $onlineTime = '-'; $onlineRoom = 'HOME';
            }
        ?>
            <tr>
                <td class="center"><?= cor_e($subject['subject_code'] ?? '') ?></td>
                <td><?= cor_e($subject['subject_name'] ?? '') ?></td>
                <td class="center"><?= cor_e(rtrim(rtrim(number_format($units, 2, '.', ''), '0'), '.')) ?></td>
                <td class="center"><?= cor_e($subject['section_code'] ?? '') ?></td>
                <td class="center"><?= cor_e($faceDay) ?></td>
                <td class="center"><?= cor_e($faceTime) ?></td>
                <td class="center"><?= cor_e($faceRoom) ?></td>
                <td class="online-cell"><?= cor_e($onlineDay) ?></td>
                <td class="online-cell"><?= cor_e($onlineTime) ?></td>
                <td class="online-cell"><?= cor_e($onlineRoom) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <table class="totals"><tr>
        <td style="width:50%;" class="center">Total Subjects: <?= (int)$totalSubjects ?></td>
        <td style="width:12%;" class="center"></td>
        <td class="right">Total Units: <?= cor_e(rtrim(rtrim(number_format($totalUnits, 2, '.', ''), '0'), '.')) ?></td>
    </tr></table>

    <table class="financial">
        <tr>
            <td style="width:54%;">
                <div class="particulars-title">PARTICULARS</div>
                <table class="assessment">
                    <tr><td colspan="2">Tuition</td><td class="amount"><?= cor_e($info['tuition_units_label'] ?? ($totalUnits . ' Units')) ?></td><td class="amount">₱ <?= number_format((float)($info['tuition_fee'] ?? ($totalUnits * 529)), 2) ?></td></tr>
                    <tr><td colspan="2">Paid by EJA Foundation</td><td></td><td class="amount">₱ <?= number_format((float)($info['eja_paid'] ?? 0), 2) ?></td></tr>
                    <tr><td colspan="3">Balance</td><td class="amount">₱ <?= number_format((float)($info['balance'] ?? max(0, ($totalUnits * 529) - (float)($info['eja_paid'] ?? 0))), 2) ?></td></tr>
                    <tr class="section-label"><td colspan="4">Miscellaneous Fee</td></tr>
                    <tr><td></td><td>Registration</td><td></td><td class="amount">₱ 400</td></tr>
                    <tr><td></td><td>Library</td><td></td><td class="amount">₱ 650</td></tr>
                    <tr><td></td><td>Athletics &amp; Sports Dev. Fee</td><td></td><td class="amount">₱ 500</td></tr>
                    <tr><td></td><td>Medical &amp; Dental</td><td></td><td class="amount">₱ 400</td></tr>
                    <tr><td></td><td>Cultural Fee</td><td></td><td class="amount">₱ 400</td></tr>
                    <tr><td></td><td>Guidance &amp; Counseling</td><td></td><td class="amount">₱ 400</td></tr>
                    <tr><td></td><td>Energy Fee</td><td></td><td class="amount">₱ 1,000</td></tr>
                    <tr><td></td><td>Laboratory Fee</td><td></td><td class="amount">₱ 600</td></tr>
                    <tr><td></td><td>Community &amp; Student Dev. Fee</td><td></td><td class="amount">₱ 600</td></tr>
                    <tr><td></td><td>Insurance</td><td></td><td class="amount">₱ 25</td></tr>
                    <tr class="total"><td colspan="3">Enrollment Fee:</td><td class="amount">₱ 4,975</td></tr>
                    <tr><td colspan="3">Supplementary Fee</td><td class="amount">₱ <?= number_format((float)($info['supplementary_fee'] ?? 0), 2) ?></td></tr>
                    <tr><td colspan="3">Other Fee</td><td class="amount">₱ <?= number_format((float)($info['other_fee'] ?? 0), 2) ?></td></tr>
                    <tr class="section-label"><td colspan="4">LESS PAYMENT:</td></tr>
                    <tr><td colspan="3" style="font-style:italic;">&nbsp;&nbsp;&nbsp;&nbsp;<?= cor_e($info['payment_reference'] ?? '') ?></td><td class="amount">₱ <?= number_format((float)($info['payment_amount'] ?? 0), 2) ?></td></tr>
                </table>
            </td>
            <td style="width:46%;">
                <div class="rules">“This is to certify that I will abide by the rules and regulations of this institution. Please refer to the back of this paper. The schedule above is reserved for one (1) day only, unless the student is officially enrolled after payment at the Accounting Office. Previous outstanding balance, if any, must be forwarded to the current / present semester.”</div>
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td><div class="signature-line"></div><div class="signature-caption">Signature over Printed Name</div></td>
            <td><div style="text-align:left;font-weight:bold;margin-left:20%;margin-bottom:2px;">PREPARED BY:</div><div class="signature-line" style="margin-top:16px;"></div><span class="signature-caption">Registrar</span></td>
        </tr>
    </table>
    <div class="note">NOTE: THE SUMMARY OF PAYMENTS AND RECEIPTS MAY BE REQUESTED FROM THE MIS OFFICE UPON REQUEST.</div>
    <div class="generated">KEEP THIS CERTIFICATE. YOU WILL BE REQUIRED TO PRESENT THIS IN ALL YOUR DEALINGS WITH THE SCHOOL.</div>
    <?php else: ?>
        <p style="text-align:center; margin-top:40px;">No enrollment record found.</p>
    <?php endif; ?>
</div>
</body>
</html>
