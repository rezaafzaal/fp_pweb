<?php
// api/cron_email.php

require_once '../config/db.php'; 
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Set durasi timeout lebih panjang
set_time_limit(120); 

date_default_timezone_set('Asia/Jakarta'); 

$today = date('Y-m-d');
$h_min_3 = date('Y-m-d', strtotime($today . ' + 3 days'));

echo "<h2>Debug Mode: Port 587 (TLS)</h2>";
echo "Server Time: $today<br>";
echo "Target Deadline: $h_min_3<br><hr>";

// Ambil data
$sql = "SELECT t.*, u.email, u.name 
        FROM tasks t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.deadline = ? AND t.status = 'ongoing'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$h_min_3]);
$tasks = $stmt->fetchAll();

if (count($tasks) > 0) {
    $mail = new PHPMailer(true);

    try {
        // --- SETTING PENTING AGAR TIDAK STUCK ---
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION; // Level 3: Tampilkan detail koneksi
        $mail->Debugoutput = 'html'; // Format log biar rapi di browser
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_EMAIL'); 
        $mail->Password   = getenv('SMTP_PASSWORD'); 
        
        // GANTI KE PORT 587 (STARTTLS)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        // OPSI TAMBAHAN: Matikan IPv6 dan Verifikasi SSL yang ketat
        // Ini sering menjadi solusi jika koneksi 'hanging' di container docker/railway
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom(getenv('SMTP_EMAIL'), 'StudyPlanner Bot');

        foreach ($tasks as $task) {
            $mail->clearAddresses();
            $mail->addAddress($task['email'], $task['name']);

            $mail->isHTML(true);
            $mail->Subject = "Reminder: Tugas H-3 Deadline!";
            
            $body  = "<p>Halo " . htmlspecialchars($task['name']) . ",</p>";
            $body .= "<p>Tugas <b>" . htmlspecialchars($task['task_name']) . "</b> deadline tanggal <b>" . $task['deadline'] . "</b>.</p>";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            echo "⏳ Sedang menghubungi Gmail untuk: " . $task['email'] . "...<br>";
            flush(); // Paksa browser menampilkan teks ini segera
            
            $mail->send();
            echo "✅ <b>BERHASIL!</b> Email terkirim.<br><br>";
        }
    } catch (Exception $e) {
        echo "<br>❌ <b>GAGAL</b><br>";
        echo "Error: " . $mail->ErrorInfo;
    }
} else {
    echo "Tidak ada tugas dengan deadline $h_min_3.";
}
?>