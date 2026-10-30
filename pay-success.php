<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

session_boot('user');
$u = current_user();
$pdo = db();
$cur = app_config()['currency'];

// ================= ১. গেটওয়ে থেকে আসা সকল সম্ভাব্য প্যারামিটার স্ক্যান =================
$ppId = trim((string)(
    $_GET['pp_id'] 
    ?? $_POST['pp_id'] 
    ?? $_GET['receipt_id'] 
    ?? $_POST['receipt_id']
    ?? $_GET['invoice_id'] 
    ?? $_POST['invoice_id']
    ?? $_GET['payment_id'] 
    ?? $_POST['payment_id']
    ?? $_GET['id'] 
    ?? $_POST['id']
    ?? $_GET['trx_id']
    ?? $_GET['transaction_id']
    ?? ($_SESSION['last_pp_id'] ?? '')
));

// ২. যদি ইউআরএলে কোনো আইডি মিসিং থাকে, ডাটাবেজে ইউজারের সর্বশেষ পেন্ডিং পেমেন্ট রেকর্ড খুঁজে নেওয়া
$targetPay = null;

if ($ppId !== '') {
    $st = $pdo->prepare("SELECT * FROM " . t('payments') . " WHERE pp_id = ? OR trx_id = ? LIMIT 1");
    $st->execute([$ppId, $ppId]);
    $targetPay = $st->fetch();
}

if (!$targetPay && $u) {
    // ইউজারের সর্বশেষ পেমেন্ট রেকর্ড রিট্রিভ করা (অটোমেটিক ফলব্যাক)
    $stPending = $pdo->prepare("SELECT * FROM " . t('payments') . " WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stPending->execute([(int)$u['id']]);
    $targetPay = $stPending->fetch();
    
    if ($targetPay && !empty($targetPay['pp_id'])) {
        $ppId = (string)$targetPay['pp_id'];
    }
}

// PipraPay v3 কনফিগারেশন লোড
$rawBaseUrl = setting('piprapay_base_url', 'https://payment.tzalmamun.com');
$cleanHost  = rtrim(preg_replace('#/api/?$#i', '', $rawBaseUrl), '/');
$apiKey     = trim((string)setting('piprapay_api_key', ''));

$isSuccess = false;
$errorMsg = '';
$creditedAmount = 0.0;
$trxId = '';
$paymentMethod = '';

// যদি ইতিপূর্বে পেমেন্ট সম্পন্ন হয়ে গিয়ে থাকে (যেমন: ওয়েব হুক দ্বারা)
if ($targetPay && $targetPay['status'] === 'completed') {
    $isSuccess = true;
    $creditedAmount = (float)$targetPay['amount'];
    $trxId = (string)($targetPay['trx_id'] ?: 'Verified');
    $paymentMethod = (string)($targetPay['payment_method'] ?: 'Online Gateway');
} 
// ৩. গেটওয়েতে ভেরিফিকেশন API কল করা
elseif ($ppId !== '' && $apiKey !== '') {
    $verifyEndpoints = [
        "{$cleanHost}/api/verify-payment",
        "{$cleanHost}/api/verify-payments"
    ];

    $verifyPayload = json_encode([
        'pp_id'          => $ppId,
        'id'             => $ppId,
        'invoice_id'     => $ppId,
        'receipt_id'     => $ppId,
        'transaction_id' => $ppId
    ], JSON_UNESCAPED_SLASHES);

    $v = null;
    $resRaw = '';

    foreach ($verifyEndpoints as $endpointUrl) {
        $ch = curl_init($endpointUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json; charset=utf-8",
                "Accept: application/json",
                "MHS-PIPRAPAY-API-KEY: {$apiKey}",
                "mh-piprapay-api-key: {$apiKey}"
            ],
            CURLOPT_POSTFIELDS     => $verifyPayload
        ]);

        $resRaw = (string)curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($resRaw, true);
        if (is_array($decoded) && (!empty($decoded['status']) || !empty($decoded['transaction_id']) || !empty($decoded['data']))) {
            $v = $decoded;
            break;
        }
    }

    $status = strtolower((string)(
        $v['status'] 
        ?? ($v['data']['status'] ?? '') 
        ?? ($v['payment_status'] ?? '')
    ));

    // ৪. পেমেন্ট সফল হলে ব্যালেন্স ক্রেডিট ও লেজার আপডেট
    if (in_array($status, ['completed', 'success', 'paid'], true)) {
        $pdo->beginTransaction();
        try {
            $payId = $targetPay ? (int)$targetPay['id'] : 0;
            
            // রো-লেভেল লক (ডাবল-ক্রেডিট প্রতিরোধ)
            $st = $pdo->prepare("SELECT * FROM " . t('payments') . " WHERE id = ? FOR UPDATE");
            $st->execute([$payId]);
            $lockedPay = $st->fetch();

            $trxId = (string)(
                $v['transaction_id'] 
                ?? $v['trx_id'] 
                ?? ($v['data']['transaction_id'] ?? '')
                ?? ($v['data']['trx_id'] ?? '')
                ?? 'TRX' . strtoupper(bin2hex(random_bytes(4)))
            );

            $paymentMethod = (string)(
                $v['payment_method'] 
                ?? $v['method'] 
                ?? ($v['data']['payment_method'] ?? 'Nagad / bKash')
            );

            $paidAmount = (float)(
                $v['amount'] 
                ?? ($v['data']['amount'] ?? ($lockedPay['amount'] ?? 100))
            );

            if ($lockedPay && $lockedPay['status'] !== 'completed') {
                $pdo->prepare("UPDATE " . t('payments') . " SET status = 'completed', trx_id = ?, payment_method = ?, raw_response = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?")
                    ->execute([$trxId, $paymentMethod, $resRaw, $lockedPay['id']]);

                // লেজার ইঞ্জিনে সরাসরি ব্যালেন্স যোগ
                $units = to_units($paidAmount);
                ledger_apply((int)$lockedPay['user_id'], 'credit_purchase', $units, 'Auto Recharge (PipraPay TrxID: ' . $trxId . ')');

                log_security((int)$lockedPay['user_id'], 'auto_recharge_success', 'Amount: ' . $paidAmount . ' BDT, Trx: ' . $trxId);
            }

            $pdo->commit();
            $isSuccess = true;
            $creditedAmount = $paidAmount;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('pay-success ledger error: ' . $e->getMessage());
            $errorMsg = 'Database transaction error: ' . $e->getMessage();
        }
    } else {
        $errorMsg = is_array($v) ? ($v['message'] ?? 'Payment was not verified by gateway.') : 'Payment confirmation pending.';
    }
} else {
    $errorMsg = 'No active payment session found to confirm.';
}

// ইউজারের ব্যালেন্স রিলোড
if ($u) {
    $u = user_by_id((int)$u['id']);
}

page_header('Payment Confirmation', $u ? 'user' : 'public');
?>

<style>
/* ========== ছিমছাম ও পরিপাটি রিসিট ডিজাইন ========== */
.receipt-wrapper {
    max-width: 480px;
    margin: 25px auto 50px auto;
    padding: 0 14px;
}
.receipt-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 26px 20px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.icon-badge-success {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #dcfce7;
    color: #15803d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
}
.icon-badge-failed {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #fee2e2;
    color: #b91c1c;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 14px;
}
.receipt-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
}
.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin: 18px 0;
    text-align: left;
}
.receipt-table td {
    padding: 9px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.receipt-table td:first-child {
    color: #64748b;
    font-weight: 500;
}
.receipt-table td:last-child {
    text-align: right;
    font-weight: 700;
    color: #1e293b;
}
</style>

<div class="receipt-wrapper">
    <div class="receipt-card">
        <?php if ($isSuccess): ?>
            <!-- সফল পেমেন্ট ভিউ -->
            <div class="icon-badge-success">&#10004;</div>
            <h2 class="receipt-title">Recharge Successful!</h2>
            <p class="muted" style="margin: 0 0 14px 0; font-size: 12.5px;">
                আপনার ব্যালেন্স সফলভাবে যোগ করা হয়েছে। আপনি এখন এসএমএস পাঠানো শুরু করতে পারেন।
            </p>

            <table class="receipt-table">
                <tr>
                    <td>Recharged Amount</td>
                    <td style="color:#16a34a; font-size:15px;">+<?= e(money4($creditedAmount)) ?> <?= e($cur) ?></td>
                </tr>
                <tr>
                    <td>Transaction ID (TrxID)</td>
                    <td><code><?= e($trxId) ?></code></td>
                </tr>
                <tr>
                    <td>Payment Method</td>
                    <td><?= e(ucfirst($paymentMethod)) ?></td>
                </tr>
                <tr>
                    <td>Payment Reference</td>
                    <td><code><?= e($ppId) ?></code></td>
                </tr>
                <?php if ($u): ?>
                    <tr style="background:#f8fafc;">
                        <td>Current Available Balance</td>
                        <td style="color:#ea580c; font-size:16px;"><?= e(money($u['balance'])) ?> <?= e($cur) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <div style="display:flex; flex-direction:column; gap:8px; margin-top:16px;">
                <a href="<?= e(base_url('send-sms.php')) ?>" class="btn" style="background:#ea580c; padding:11px; font-weight:700;">
                    Send SMS Now &rarr;
                </a>
                <a href="<?= e(base_url('dashboard.php')) ?>" class="btn sec" style="padding:10px;">
                    Go to Dashboard
                </a>
            </div>

        <?php else: ?>
            <!-- ব্যর্থ বা পেন্ডিং ভিউ -->
            <div class="icon-badge-failed">&#10007;</div>
            <h2 class="receipt-title" style="color:#b91c1c;">Recharge Pending / Failed</h2>
            <p class="muted" style="margin: 0 0 16px 0; color:#991b1b; font-size:13px;">
                <?= e($errorMsg ?: 'পেমেন্ট গেটওয়ে থেকে কনফার্মেশন পাওয়া যায়নি।') ?>
            </p>

            <?php if ($ppId): ?>
                <p class="muted" style="font-size:11.5px;">Reference ID: <code><?= e($ppId) ?></code></p>
            <?php endif; ?>

            <div style="display:flex; flex-direction:column; gap:8px; margin-top:20px;">
                <a href="<?= e(base_url('pay-success.php')) ?>" class="btn" style="background:#059669; padding:11px;">
                    &#128260; Re-Check Payment Status
                </a>
                <a href="<?= e(base_url('invoices.php')) ?>" class="btn sec" style="padding:10px;">
                    Return to Recharge Page
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php page_footer(); ?>
