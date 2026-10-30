<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$ppId    = trim((string)($_GET['pp_id'] ?? $_POST['pp_id'] ?? ''));
$apiKey  = "YOUR_PIPRAPAY_API_KEY";
$baseUrl = "https://payment.yourdomain.com";
$cleanHost = rtrim(preg_replace('#/api/?$#i', '', $baseUrl), '/');

if ($ppId !== '') {
    // PipraPay API ভেরিফাই কল
    $ch = curl_init("{$cleanHost}/api/verify-payment");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "Accept: application/json",
            "MHS-PIPRAPAY-API-KEY: {$apiKey}"
        ],
        CURLOPT_POSTFIELDS     => json_encode(['pp_id' => $ppId])
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $v = json_decode((string)$res, true);

    if (strtolower((string)($v['status'] ?? '')) === 'completed') {
        $st = db()->prepare("SELECT * FROM payments WHERE pp_id = ? LIMIT 1");
        $st->execute([$ppId]);
        $pay = $st->fetch();

        if ($pay && $pay['status'] !== 'completed') {
            // ১. পেমেন্ট কমপ্লিট আপডেট
            db()->prepare("UPDATE payments SET status = 'completed', trx_id = ?, payment_method = ?, updated_at = NOW() WHERE pp_id = ?")
                ->execute([$v['transaction_id'] ?? '', $v['payment_method'] ?? '', $ppId]);

            // ২. মেম্বারশিপ বা কোর্স ১০০% অটো-আনলক
            db()->prepare("UPDATE users SET course_access = 1, status = 'approved' WHERE id = ?")
                ->execute([(int)$pay['user_id']]);
        }
        echo "<h1>পেমেন্ট সফল হয়েছে! আপনার কোর্স চালু হয়েছে।</h1><a href='/user/index.php'>ড্যাশবোর্ডে যান</a>";
        exit;
    }
}
echo "<h1>পেমেন্ট ভেরিফাই হয়নি বা বাতিল হয়েছে।</h1>";
