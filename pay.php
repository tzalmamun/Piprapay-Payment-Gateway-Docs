<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php'; // আপনার ডাটাবেস ও সেশন কনফিগ

$me = current_user();
if (!$me) redirect('/login.php');

$apiKey  = "YOUR_PIPRAPAY_API_KEY";
$baseUrl = "https://payment.yourdomain.com"; // আপনার PipraPay ডোমেইন
$cleanHost = rtrim(preg_replace('#/api/?$#i', '', $baseUrl), '/');

$amount    = 500;
$uid       = (int)$me['id'];
$siteUrl   = "https://" . $_SERVER['HTTP_HOST'];
$redirect  = $siteUrl . "/pay-success.php?user_id={$uid}";
$webhook   = $siteUrl . "/api/piprapay-webhook.php?user_id={$uid}";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'full_name'       => $me['name'] ?? 'Student ' . $uid,
        'customer_email'  => $me['email'] ?? 'student@gmail.com',
        'email'           => $me['email'] ?? 'student@gmail.com',
        'customer_mobile' => $me['whatsapp'] ?? '01700000000',
        'mobile_number'   => $me['whatsapp'] ?? '01700000000',
        'phone'           => $me['whatsapp'] ?? '01700000000',
        'amount'          => (string)$amount,
        'currency'        => 'BDT',
        'return_url'      => $redirect,
        'redirect_url'    => $redirect,
        'cancel_url'      => $siteUrl . "/courses.php",
        'webhook_url'     => $webhook
    ];

    $ch = curl_init("{$cleanHost}/api/checkout/redirect");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json; charset=utf-8",
            "Accept: application/json",
            "MHS-PIPRAPAY-API-KEY: {$apiKey}"
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES)
    ]);

    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode((string)$res, true);

    $ppUrl = $data['pp_url'] ?? $data['redirect_url'] ?? '';
    $ppId  = $data['pp_id'] ?? '';

    if (!empty($ppUrl)) {
        // ডাটাবেসে সেভ
        $st = db()->prepare("INSERT INTO payments (user_id, pp_id, amount, status) VALUES (?, ?, ?, 'pending')");
        $st->execute([$uid, $ppId, $amount]);
        
        header("Location: " . $ppUrl);
        exit;
    } else {
        $msg = $data['message'] ?? 'পেমেন্ট শুরু করা যায়নি।';
        $err = is_array($msg) ? json_encode($msg) : (string)$msg;
    }
}
?>
<!-- পেমেন্ট বাটন HTML -->
<form method="post">
  <button type="submit">বিকাশ/নগদে পেমেন্ট করুন (৳<?= $amount ?>)</button>
</form>
