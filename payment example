<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$u = require_login();
$uid = (int)$u['id'];
$cur = app_config()['currency'];
$pdo = db();

// ================= ১. পেমেন্টস টেবিল অটো-মাইগ্রেশন =================
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . t('payments') . " (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT UNSIGNED NOT NULL,
        `pp_id` VARCHAR(100) NULL,
        `purpose` VARCHAR(50) NOT NULL DEFAULT 'sms_recharge',
        `amount` DECIMAL(10,4) NOT NULL,
        `currency` VARCHAR(10) NOT NULL DEFAULT 'BDT',
        `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
        `payment_method` VARCHAR(50) NULL,
        `trx_id` VARCHAR(100) NULL,
        `sender_number` VARCHAR(30) NULL,
        `raw_response` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        KEY `idx_pay_user` (`user_id`),
        KEY `idx_pay_ppid` (`pp_id`),
        KEY `idx_pay_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) {}

// PipraPay v3 কনফিগারেশন লোড
$rawBaseUrl = setting('piprapay_base_url', 'https://payment.tzalmamun.com');
$cleanHost  = rtrim(preg_replace('#/api/?$#i', '', $rawBaseUrl), '/');
$apiKey     = trim((string)setting('piprapay_api_key', ''));

// ডিফল্ট ইমেইল নির্বাচন (INVALID_EMAIL স্থায়ী সমাধান)
$userEmail = strtolower(trim((string)($u['email'] ?? '')));
$defaultEmail = 'tzalmamun@gmail.com';
if (filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    $domain = substr(strrchr($userEmail, "@"), 1);
    if (!str_contains($domain, 'bulksms') && !str_contains($domain, 'localhost') && !str_contains($domain, 'example.com')) {
        $defaultEmail = $userEmail;
    }
}

$err = '';

// ================= ২. PipraPay v3 পেমেন্ট প্রসেসিং =================
if (post_guard()) {
    $amountInput = (float)($_POST['amount'] ?? 0);

    if ($amountInput < 50) {
        $err = 'মিনিমাম ডিপোজিট ৫০ টাকা হতে হবে।';
    } elseif ($apiKey === '' || $apiKey === 'YOUR_MHS_PIPRAPAY_API_KEY') {
        $err = 'PipraPay API Key কনফিগার করা নেই। অ্যাডমিন প্যানেলের General Settings থেকে API Key সেট করুন।';
    } else {
        // ইমেইল নিশ্চিতকরণ
        $inputEmail = strtolower(trim((string)($_POST['customer_email'] ?? '')));
        $customerEmail = filter_var($inputEmail, FILTER_VALIDATE_EMAIL) ? $inputEmail : $defaultEmail;
        $domain = substr(strrchr($customerEmail, "@"), 1);
        if (str_contains($domain, 'bulksms') || str_contains($domain, 'localhost') || !str_contains($domain, '.')) {
            $customerEmail = 'tzalmamun@gmail.com';
        }

        // ফোন নম্বর নিশ্চিতকরণ
        $phone = preg_replace('/[^0-9]/', '', (string)($_POST['customer_mobile'] ?? ($u['phone'] ?? ($u['mobile'] ?? '01777811418'))));
        if (str_starts_with($phone, '8801') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        }
        if (strlen($phone) < 11 || !preg_match('/^01[3-9]\d{8}$/', $phone)) {
            $phone = '01777811418';
        }

        // গ্রাহক নাম
        $name = trim((string)($u['name'] ?: 'TZ Member ' . $uid));
        $name = preg_replace('/[^\p{L}\p{N}\s\.\-_]/u', '', $name);
        if (empty($name)) {
            $name = 'Member ' . $uid;
        }

        // ডোমেইন হোয়াইটলিস্ট নিশ্চিতকরণ (www রিমুভ)
        $rawHost = $_SERVER['HTTP_HOST'] ?? 'tzalmamun.com';
        $cleanHostName = preg_replace('/^www\./i', '', explode(':', $rawHost)[0]);
        $siteDomain = 'https://' . ($cleanHostName ?: 'tzalmamun.com');

        $redirectUrl = $siteDomain . '/pay-success.php?user_id=' . $uid;
        $webhookUrl  = $siteDomain . '/api/piprapay-webhook.php?user_id=' . $uid;
        $cancelUrl   = $siteDomain . '/invoices.php';

        $amountFormatted = (floor($amountInput) == $amountInput) ? (string)((int)$amountInput) : (string)round($amountInput, 2);

        // সম্পূর্ণ সুরক্ষিত পে-লোড (আপনার ওয়ার্কিং স্ক্রিপ্টের হুবহু অল্টারনেটিভ প্যারামিটার সহ)
        $payload = [
            'mobile_number'   => $phone,
            'mobile'          => $phone,
            'phone_number'    => $phone,
            'phone'           => $phone,
            'customer_mobile' => $phone,
            'customer_phone'  => $phone,
            'email_mobile'    => $phone,
            'whatsapp_number' => $phone,

            'customer_email'  => $customerEmail,
            'email'           => $customerEmail,
            'email_address'   => $customerEmail,

            'full_name'       => $name,
            'customer_name'   => $name,
            'amount'          => $amountFormatted,
            'currency'        => 'BDT',

            'return_url'      => $redirectUrl,
            'redirect_url'    => $redirectUrl,
            'return_type'     => 'GET',
            'cancel_url'      => $cancelUrl,
            'webhook_url'     => $webhookUrl,
            'metadata'        => [
                'user_id' => (string)$uid,
                'purpose' => 'sms_recharge'
            ]
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // আপনার ওয়ার্কিং স্ক্রিপ্ট অনুযায়ী এন্ডপয়েন্ট তালিকা
        $endpoints = [
            "{$cleanHost}/api/create-charge",
            "{$cleanHost}/api/checkout/redirect"
        ];

        $resData = null;
        $httpCode = 0;
        $lastCurlErr = '';

        foreach ($endpoints as $apiUrl) {
            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json; charset=utf-8",
                    "Accept: application/json",
                    "MHS-PIPRAPAY-API-KEY: {$apiKey}",
                    "mh-piprapay-api-key: {$apiKey}"
                ],
                CURLOPT_POSTFIELDS     => $jsonPayload
            ]);

            $response    = curl_exec($ch);
            $httpCode    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $lastCurlErr = curl_error($ch);
            curl_close($ch);

            $decoded = json_decode((string)$response, true);
            if (is_array($decoded) && (!empty($decoded['pp_url']) || !empty($decoded['redirect_url']) || !empty($decoded['url']))) {
                $resData = $decoded;
                break;
            } else {
                $resData = $decoded;
            }
        }

        $ppUrl = $resData['pp_url'] ?? $resData['redirect_url'] ?? $resData['url'] ?? '';
        $ppId  = (string)($resData['pp_id'] ?? $resData['invoice_id'] ?? $resData['id'] ?? '');

        if (!empty($ppUrl)) {
            $ins = $pdo->prepare("INSERT INTO " . t('payments') . " (user_id, pp_id, purpose, amount, currency, status, sender_number, raw_response) VALUES (?, ?, 'sms_recharge', ?, 'BDT', 'pending', ?, ?)");
            $ins->execute([$uid, $ppId, $amountInput, $phone, (string)$response]);

            header("Location: {$ppUrl}");
            exit;
        } else {
            if (is_array($resData)) {
                if (isset($resData['message'])) {
                    $err = is_array($resData['message']) 
                        ? implode('; ', array_map(fn($m) => is_array($m) ? implode(', ', $m) : (string)$m, $resData['message'])) 
                        : (string)$resData['message'];
                } elseif (isset($resData['error'])) {
                    $err = is_array($resData['error']) 
                        ? (isset($resData['error']['message']) ? (string)$resData['error']['message'] : json_encode($resData['error'], JSON_UNESCAPED_UNICODE)) 
                        : (string)$resData['error'];
                } else {
                    $err = 'পেমেন্ট গেটওয়েতে সংযোগ সফল হয়নি (HTTP: ' . $httpCode . ')।';
                }
            } else {
                $err = 'গেটওয়ে সার্ভার থেকে রেসপন্স আসেনি: ' . ($lastCurlErr ?: 'HTTP ' . $httpCode);
            }
        }
    }
}

// সাম্প্রতিক ৫টি রিচার্জ হিস্ট্রি
$histStmt = $pdo->prepare("SELECT * FROM " . t('payments') . " WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$histStmt->execute([$uid]);
$recentPayments = $histStmt->fetchAll();

page_header('Recharge Balance');
?>

<style>
/* ========== আগের মতো ছিমছাম, ছোট ও কম্প্যাক্ট ডিজাইন ========== */
.recharge-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.02);
}
.balance-display {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.min-deposit-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 12px;
}
.amount-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 8px 0 12px 0;
}
.preset-btn {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s ease;
}
.preset-btn:hover, .preset-btn.active {
    background: #ea580c;
    color: #ffffff;
    border-color: #ea580c;
}
.recharge-input {
    width: 100% !important;
    max-width: 240px !important;
    padding: 8px 12px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    background: #ffffff !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 6px !important;
    box-sizing: border-box !important;
}
.recharge-input:focus {
    border-color: #ea580c !important;
    outline: none !important;
}
.tz-pay-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #ea580c !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    padding: 10px 20px !important;
    border-radius: 6px !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: 0 2px 6px rgba(234, 88, 12, 0.25);
    transition: all 0.15s ease;
}
.tz-pay-btn:hover {
    background: #c2410c !important;
}
.payment-info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 16px;
    font-size: 12px;
}
</style>

<div class="recharge-card">
    <?php if ($err): ?>
        <div class="alert error" style="margin-bottom: 12px; font-size: 12.5px; padding: 8px 12px;">
            <?= e($err) ?>
        </div>
    <?php endif; ?>

    <!-- ব্যালেন্স ও রেট ডিসপ্লে -->
    <div class="balance-display">
        <div>
            <span class="muted" style="font-size:11.5px; display:block;">Current Available Balance</span>
            <b style="font-size:22px; color:#111827;"><?= e(money($u['balance'])) ?> <?= e($cur) ?></b>
        </div>
        <div>
            <span class="muted" style="font-size:11.5px; display:block;">Rate per SMS Segment</span>
            <b style="font-size:14px; color:#ea580c;"><?= e(money4(from_units(user_unit_price($u)))) ?> <?= e($cur) ?> / seg</b>
        </div>
    </div>

    <!-- মিনিমাম ডিপোজিট নোটিশ -->
    <div class="min-deposit-badge">
        <span>&#9888;</span> মিনিমাম ডিপোজিট ৫০ টাকা (Minimum Recharge: 50 <?= e($cur) ?>)
    </div>

    <h2 style="margin:0 0 4px 0; font-size:15px;">Instant Auto Recharge &middot; TZ Pay</h2>
    <p class="muted" style="margin:0 0 10px 0; font-size:12px;">
        বিকাশ, নগদ বা রকেটের মাধ্যমে স্বয়ংক্রিয়ভাবে অ্যাকাউন্ট রিচার্জ করুন। পেমেন্ট সম্পন্ন হলেই ব্যালেন্স অটো যোগ হবে।
    </p>

    <!-- ছোট ও পরিপাটি রিচার্জ ফর্ম -->
    <form method="post" id="pipraRechargeForm">
        <?= csrf_field() ?>
        
        <input type="hidden" name="customer_email" value="<?= e($defaultEmail) ?>">
        <input type="hidden" name="customer_mobile" value="01777811418">

        <label style="font-size:12.5px; font-weight:600; margin:0 0 4px 0;">Select or Enter Recharge Amount (<?= e($cur) ?>)</label>
        
        <!-- প্রিসেট বাটন -->
        <div class="amount-presets">
            <button type="button" class="preset-btn active" onclick="setAmount(50)">50 <?= e($cur) ?></button>
            <button type="button" class="preset-btn" onclick="setAmount(100)">100 <?= e($cur) ?></button>
            <button type="button" class="preset-btn" onclick="setAmount(300)">300 <?= e($cur) ?></button>
            <button type="button" class="preset-btn" onclick="setAmount(500)">500 <?= e($cur) ?></button>
            <button type="button" class="preset-btn" onclick="setAmount(1000)">1,000 <?= e($cur) ?></button>
            <button type="button" class="preset-btn" onclick="setAmount(2000)">2,000 <?= e($cur) ?></button>
        </div>

        <div>
            <input type="number" name="amount" id="rechargeAmount" class="recharge-input" value="50" min="50" step="any" required placeholder="Enter amount">
            <small class="muted" id="minAlert" style="display:none; margin-top:3px; font-size:11px; color:#dc2626; font-weight:600;">
                সর্বনিম্ন রিচার্জ অ্যামাউন্ট ৫০ টাকা হতে হবে।
            </small>
        </div>

        <!-- সাবমিট বাটন -->
        <div style="margin-top:14px;">
            <button type="submit" id="paySubmitBtn" class="tz-pay-btn">
                <span>&#128179;</span> Pay With bKash / Nagad / Rocket
            </button>
        </div>
    </form>

    <div class="payment-info-box">
        <strong style="color:#1e293b;">&#9889; Supported Payment Methods:</strong>
        <span class="muted">bKash, Nagad, Rocket, Upay &amp; Card supported via <b>TZ Auto Payment Gateway</b>.</span>
    </div>
</div>

<!-- সাম্প্রতিক রিচার্জ হিস্ট্রি -->
<?php if ($recentPayments): ?>
    <h2 style="font-size:14px; margin-bottom:6px;">Recent Recharge Transactions</h2>
    <div class="tbl">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>TrxID</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentPayments as $p): ?>
                    <tr>
                        <td><?= e(fmt_dt($p['created_at'])) ?></td>
                        <td><code><?= e($p['pp_id'] ?: '-' . (int)$p['id']) ?></code></td>
                        <td><b><?= e(money4($p['amount'])) ?> <?= e($cur) ?></b></td>
                        <td><code><?= e($p['trx_id'] ?: '-') ?></code></td>
                        <td><?= e($p['payment_method'] ?: 'Auto Gateway') ?></td>
                        <td><?= status_badge($p['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function setAmount(val) {
    var amtInput = document.getElementById('rechargeAmount');
    amtInput.value = val;
    
    var btns = document.querySelectorAll('.preset-btn');
    btns.forEach(function(b) {
        if (b.textContent.trim().indexOf(val.toString()) === 0) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });

    validateAmount();
}

function validateAmount() {
    var amtInput = document.getElementById('rechargeAmount');
    var minAlert = document.getElementById('minAlert');
    var amount = parseFloat(amtInput.value) || 0;

    if (amount < 50) {
        minAlert.style.display = 'block';
    } else {
        minAlert.style.display = 'none';
    }
}

document.getElementById('rechargeAmount').addEventListener('input', validateAmount);

// ডাবল-ক্লিক লক
document.getElementById('pipraRechargeForm').addEventListener('submit', function(e) {
    var amt = parseFloat(document.getElementById('rechargeAmount').value) || 0;
    if (amt < 50) {
        e.preventDefault();
        alert('মিনিমাম ডিপোজিট ৫০ টাকা হতে হবে।');
        return;
    }
    var btn = document.getElementById('paySubmitBtn');
    btn.disabled = true;
    btn.innerHTML = 'Connecting to Gateway...';
});
</script>

<?php page_footer(); ?>
