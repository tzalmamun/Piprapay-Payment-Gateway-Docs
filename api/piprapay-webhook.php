<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/bootstrap.php';

$apiKey = "YOUR_PIPRAPAY_API_KEY";

// হেডার থেকে সিক্রেট কি যাচাই
$headers = function_exists('getallheaders') ? getallheaders() : [];
$receivedKey = '';
foreach ($headers as $k => $v) {
    if (in_array(strtolower($k), ['mhs-piprapay-api-key', 'mh-piprapay-api-key'], true)) {
        $receivedKey = (string)$v;
        break;
    }
}

if (!hash_equals($apiKey, $receivedKey)) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode((string)file_get_contents('php://input'), true);

if (strtolower((string)($data['status'] ?? '')) === 'completed') {
    $ppId  = $data['pp_id'] ?? '';
    $trxId = $data['transaction_id'] ?? '';

    $st = db()->prepare("SELECT * FROM payments WHERE pp_id = ? LIMIT 1");
    $st->execute([$ppId]);
    $pay = $st->fetch();

    if ($pay && $pay['status'] !== 'completed') {
        db()->prepare("UPDATE payments SET status = 'completed', trx_id = ?, updated_at = NOW() WHERE pp_id = ?")
            ->execute([$trxId, $ppId]);

        // অটো কোর্স আনলক
        db()->prepare("UPDATE users SET course_access = 1, status = 'approved' WHERE id = ?")
            ->execute([(int)$pay['user_id']]);
    }
}

http_response_code(200);
echo json_encode(['status' => true, 'message' => 'Webhook Processed']);
exit;
