<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$page_title = 'ডেভেলপার ডকুমেন্টেশন ও অটোমেশন হাব | ' . settings()['site_name'];
require __DIR__ . '/includes/header.php';

$config = settings();
$apiUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'tzalmamun.com') . '/api/license.php';
?>

<!-- ========================================================
     DEVELOPER DOCS PORTAL CSS (Dark Code Blocks + 1-Click Copy)
     ======================================================== -->
<style>
:root {
  --dc-bg: #f8fafc;
  --dc-card: #ffffff;
  --dc-border: #e2e8f0;
  --dc-text: #0f172a;
  --dc-muted: #64748b;
  --dc-blue: #2563eb;
  --dc-blue-hover: #1d4ed8;
  --dc-green: #059669;
  --dc-code-bg: #090d16;
  --dc-code-border: #1e293b;
  --dc-radius: 16px;
}

body {
  background-color: var(--dc-bg) !important;
  color: var(--dc-text);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Noto Sans Bengali", sans-serif;
  -webkit-font-smoothing: antialiased;
}

.dc-wrap {
  max-width: 960px;
  margin: 0 auto;
  padding: 24px 14px 80px;
}

/* পেজ হেডার */
.dc-hero {
  text-align: center;
  margin-bottom: 28px;
}
.dc-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: var(--dc-blue);
  font-size: 0.78rem;
  font-weight: 800;
  padding: 4px 14px;
  border-radius: 999px;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.dc-title {
  font-size: 2rem;
  font-weight: 900;
  color: var(--dc-text);
  margin: 0 0 8px;
  letter-spacing: -0.015em;
}
.dc-desc {
  font-size: 0.95rem;
  color: var(--dc-muted);
  max-width: 640px;
  margin: 0 auto;
  line-height: 1.6;
}

/* কুইক ন্যাভিগেশন পিলস */
.dc-nav-pills {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 8px;
  margin-bottom: 28px;
  -webkit-overflow-scrolling: touch;
}
.dc-nav-btn {
  padding: 8px 16px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid var(--dc-border);
  color: #334155;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 700;
  white-space: nowrap;
  transition: all 0.15s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.dc-nav-btn:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: var(--dc-blue);
}

/* সেকশন কার্ড */
.dc-section-card {
  background: var(--dc-card);
  border: 1px solid var(--dc-border);
  border-radius: var(--dc-radius);
  padding: 26px 20px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}
@media (min-width: 680px) {
  .dc-section-card { padding: 32px 28px; }
}

.dc-sec-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
  border-bottom: 1px solid var(--dc-border);
  padding-bottom: 14px;
}
.dc-sec-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: var(--dc-blue);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.dc-sec-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--dc-text);
  margin: 0;
}
.dc-sec-desc {
  font-size: 0.9rem;
  color: var(--dc-muted);
  line-height: 1.6;
  margin: 0 0 16px;
}

/* কোড বক্স ও হেডার বার */
.dc-code-block {
  background: var(--dc-code-bg);
  border: 1px solid var(--dc-code-border);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 18px;
}
.dc-code-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #0f172a;
  border-bottom: 1px solid var(--dc-code-border);
}
.dc-file-tag {
  font-family: 'JetBrains Mono', Consolas, monospace;
  font-size: 0.8rem;
  font-weight: 700;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 6px;
}
.dc-btn-copy {
  background: #1e293b;
  color: #f8fafc;
  border: 1px solid #334155;
  border-radius: 6px;
  padding: 5px 12px;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.dc-btn-copy:hover {
  background: var(--dc-blue);
  border-color: var(--dc-blue);
}

.dc-pre {
  margin: 0;
  padding: 16px;
  max-height: 420px;
  overflow: auto;
  font-family: 'JetBrains Mono', Consolas, monospace;
  font-size: 0.84rem;
  line-height: 1.65;
  color: #e2e8f0;
}

/* হাইলাইট ইনফো বক্স */
.dc-alert {
  background: #fffbeb;
  border: 1px solid #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 0.86rem;
  color: #92400e;
  line-height: 1.6;
  margin-bottom: 16px;
}
</style>

<div class="dc-wrap">

  <!-- হেডার -->
  <div class="dc-hero">
    <div class="dc-pill">Developer Hub &amp; Integration Docs</div>
    <h1 class="dc-title">সেন্ট্রাল পিএইচপি কোড ও ডকুমেন্টেশন সিস্টেম</h1>
    <p class="dc-desc">
      PipraPay v3 অটো পেমেন্ট গেটওয়ে, AI মাস্টার প্রম্পট এবং সেন্ট্রাল লাইসেন্স ইঞ্জিনের সম্পূর্ণ কার্যপদ্ধতি ও কপি-রেডি সোর্স কোড।
    </p>
  </div>

  <!-- কুইক জাম্প ন্যাভিগেশন -->
  <div class="dc-nav-pills">
    <a href="#aiPrompt" class="dc-nav-btn">🤖 AI Master Prompt</a>
    <a href="#paymentCodes" class="dc-nav-btn">💳 PipraPay v3 Codes</a>
    <a href="#databaseSql" class="dc-nav-btn">🗄️ Database SQL</a>
    <a href="#licenseGuard" class="dc-nav-btn">🔑 License Hard-Lock</a>
    <a href="#rulesSection" class="dc-nav-btn">⚠️ Crucial Rules</a>
  </div>

  <!-- ==================== ১. মাস্টার এআই প্রম্পট ==================== -->
  <section class="dc-section-card" id="aiPrompt">
    <div class="dc-sec-head">
      <div class="dc-sec-icon">🤖</div>
      <div>
        <h2 class="dc-sec-title">যেকোনো AI-কে দেওয়ার জন্য মাস্টার প্রম্পট (Universal Master Prompt)</h2>
      </div>
    </div>
    <p class="dc-sec-desc">
      ভবিষ্যতে যেকোনো নতুন পিএইচপি স্ক্রিপ্টে অটো পেমেন্ট বসাতে চাইলে নিচের প্রম্পটটি ১-ক্লিকে কপি করে ChatGPT বা Claude-এ দিন।
    </p>

    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">📄 Master-AI-Prompt.txt</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codeAiPrompt', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codeAiPrompt"><code>You are an expert PHP and Payment Gateway integration specialist.
Please integrate PipraPay v3 (Self-Hosted / Hosted) automated payment gateway into my PHP project following these STRICT, tested, and proven technical rules:

--- STRICT PIPRAPAY V3 SPECIFICATIONS ---
1. AUTHENTICATION HEADER:
   - Must send header: "MHS-PIPRAPAY-API-KEY: {API_KEY}" (Note the letter 'S' in MHS! Older 'mh-piprapay-api-key' fails on v3).
   - Also send: "Content-Type: application/json; charset=utf-8" and "Accept: application/json".

2. ENDPOINT RULES & URL NORMALIZATION:
   - If user sets Base URL as "https://payment.domain.com/api" or "https://payment.domain.com", automatically normalize it to remove trailing "/api" to avoid double "/api/api" bugs:
     $cleanHost = rtrim(preg_replace('#/api/?$#i', '', $rawBaseUrl), '/');
   - Primary Checkout Endpoint: "{$cleanHost}/api/checkout/redirect" (Fallback: "{$cleanHost}/api/create-charge").
   - Verification Endpoint: "{$cleanHost}/api/verify-payment" (Fallback: "{$cleanHost}/api/verify-payments").

3. REQUIRED PAYLOAD PARAMETERS:
   - 'full_name'       => Customer name string
   - 'customer_email'  => Valid customer email (e.g. user@gmail.com)
   - 'email'           => Valid customer email
   - 'customer_mobile' => 11-digit valid BD phone (e.g. 017XXXXXXXX)
   - 'mobile_number'   => 11-digit valid BD phone
   - 'phone'           => 11-digit valid BD phone
   - 'amount'          => Price as string (e.g. "500")
   - 'currency'        => 'BDT'
   - 'return_url'      => Absolute URL to pay-success.php (MUST include return_url and redirect_url)
   - 'redirect_url'    => Absolute URL to pay-success.php
   - 'cancel_url'      => Absolute cancel URL
   - 'webhook_url'     => Absolute URL to api/piprapay-webhook.php

4. THREE REQUIRED FILES ARCHITECTURE:
   a) pay.php: Creates charge via cURL, inserts pending record in 'payments' table, extracts 'pp_url' and redirects user: header("Location: " . $pp_url);
   b) pay-success.php: Catches 'pp_id' from GET/POST, queries "{$cleanHost}/api/verify-payment", if status is 'completed', updates payments table and unlocks access idempotently.
   c) api/piprapay-webhook.php: Server-to-server listener. Validates 'MHS-PIPRAPAY-API-KEY', checks payload 'status' == 'completed', updates payments table and unlocks access idempotently, returns HTTP 200 JSON.

5. ERROR HANDLING:
   - API error responses might return an array or string. Always ensure error variables are converted to string before passing to htmlspecialchars/e() to prevent fatal TypeError.

Please review my project code attached below and implement the payment gateway following these exact rules:
[এখানে আপনার প্রজেক্টের কোড পেস্ট করুন]</code></pre>
    </div>
  </section>

  <!-- ==================== ২. PIPRAPAY V3 সম্পূর্ণ সোর্স কোড ==================== -->
  <section class="dc-section-card" id="paymentCodes">
    <div class="dc-sec-head">
      <div class="dc-sec-icon">💳</div>
      <div>
        <h2 class="dc-sec-title">PipraPay v3 অটো পেমেন্ট কোডসমূহ (Plug &amp; Play)</h2>
      </div>
    </div>
    <p class="dc-sec-desc">
      নিচে আপনার ওয়েবসাইটের সম্পূর্ণ কার্যকরী ৩টি ফাইল দেওয়া হলো। ভবিষ্যতে নতুন প্রজেক্টে হুবহু কপি করে ব্যবহার করতে পারবেন।
    </p>

    <!-- ফাইল ১: pay.php -->
    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">📄 pay.php (পেমেন্ট তৈরি ও PipraPay-তে রিডাইরেক্ট)</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codePayPhp', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codePayPhp"><code>&lt;?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$me = current_user();
if (!$me) redirect('/login.php');

$uid     = (int)$me['id'];
$apiKey  = "YOUR_MHS_PIPRAPAY_API_KEY";
$baseUrl = "https://payment.tzalmamun.com"; // আপনার PipraPay ডোমেইন

$cleanHost = rtrim(preg_replace('#/api/?$#i', '', $baseUrl), '/');
$siteUrl   = "https://" . $_SERVER['HTTP_HOST'];
$redirect  = $siteUrl . "/pay-success.php?user_id={$uid}";
$webhook   = $siteUrl . "/api/piprapay-webhook.php?user_id={$uid}";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = 500;
    $email  = trim((string)($me['email'] ?: 'tzalmamun@gmail.com'));
    $phone  = preg_replace('/[^0-9]/', '', (string)($me['whatsapp'] ?: '01777811418'));

    $payload = [
        'full_name'       => $me['name'] ?? 'Member ' . $uid,
        'customer_email'  => $email,
        'email'           => $email,
        'customer_mobile' => $phone,
        'mobile_number'   => $phone,
        'phone'           => $phone,
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
        db()->prepare("INSERT INTO payments (user_id, pp_id, amount, status) VALUES (?, ?, ?, 'pending')")
            ->execute([$uid, $ppId, $amount]);
        header("Location: " . $ppUrl);
        exit;
    } else {
        $err = $data['message'] ?? 'পেমেন্ট শুরু করা যায়নি।';
    }
}
?&gt;
&lt;!-- ফর্ম বাটন --&gt;
&lt;form method="post"&gt;
  &lt;button type="submit"&gt;বিকাশ/নগদে পেমেন্ট করুন (৳500)&lt;/button&gt;
&lt;/form&gt;</code></pre>
    </div>

    <!-- ফাইল ২: pay-success.php -->
    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">📄 pay-success.php (ব্রাউজার রিডাইরেক্ট ও ভেরিফাই)</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codePaySuccess', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codePaySuccess"><code>&lt;?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$ppId    = trim((string)($_GET['pp_id'] ?? $_POST['pp_id'] ?? ''));
$apiKey  = "YOUR_MHS_PIPRAPAY_API_KEY";
$baseUrl = "https://payment.tzalmamun.com";
$cleanHost = rtrim(preg_replace('#/api/?$#i', '', $baseUrl), '/');

if ($ppId !== '') {
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
            db()->prepare("UPDATE payments SET status = 'completed', trx_id = ?, payment_method = ?, updated_at = NOW() WHERE pp_id = ?")
                ->execute([$v['transaction_id'] ?? '', $v['payment_method'] ?? '', $ppId]);

            // ১০০% অটো-কোর্স ও মেম্বারশিপ আনলক
            db()->prepare("UPDATE users SET course_access = 1, status = 'approved' WHERE id = ?")
                ->execute([(int)$pay['user_id']]);
        }
        echo "&lt;h1&gt;পেমেন্ট সফল হয়েছে! কোর্স আনলক সম্পন্ন।&lt;/h1&gt;&lt;a href='/user/index.php'&gt;ড্যাশবোর্ডে প্রবেশ করুন&lt;/a&gt;";
        exit;
    }
}
echo "&lt;h1&gt;পেমেন্ট সফল হয়নি বা বাতিল হয়েছে।&lt;/h1&gt;";</code></pre>
    </div>

    <!-- ফাইল ৩: api/piprapay-webhook.php -->
    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">📄 api/piprapay-webhook.php (সার্ভার-টু-সার্ভার ওয়েবহুক)</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codeWebhook', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codeWebhook"><code>&lt;?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/bootstrap.php';

$apiKey = "YOUR_MHS_PIPRAPAY_API_KEY";

// হেডার যাচাই
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

        // ব্যাকগ্রাউন্ডে স্বয়ংক্রিয় কোর্স ও মেম্বারশিপ আনলক
        db()->prepare("UPDATE users SET course_access = 1, status = 'approved' WHERE id = ?")
            ->execute([(int)$pay['user_id']]);
    }
}

http_response_code(200);
echo json_encode(['status' => true, 'message' => 'Webhook Processed']);
exit;</code></pre>
    </div>
  </section>

  <!-- ==================== ৩. ডাটাবেস টেবিল স্কিমা ==================== -->
  <section class="dc-section-card" id="databaseSql">
    <div class="dc-sec-head">
      <div class="dc-sec-icon">🗄️</div>
      <div>
        <h2 class="dc-sec-title">ডাটাবেস টেবিল স্কিমা (MySQL Schema)</h2>
      </div>
    </div>
    <p class="dc-sec-desc">
      যেকোনো নতুন সিস্টেমে পেমেন্ট সংরক্ষণের জন্য নিচের SQL কোডটি ডাটাবেসে রান করুন।
    </p>

    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">📄 schema.sql</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codeSql', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codeSql"><code>CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pp_id VARCHAR(50) NULL,
    purpose VARCHAR(50) NOT NULL DEFAULT 'course',
    course_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'BDT',
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NULL,
    trx_id VARCHAR(100) NULL,
    sender_number VARCHAR(30) NULL,
    raw_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    INDEX(user_id),
    INDEX(pp_id),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></pre>
    </div>
  </section>

  <!-- ==================== ৪. গুরুত্বপূর্ণ ৪টি টেকনিক্যাল রুলস ==================== -->
  <section class="dc-section-card" id="rulesSection">
    <div class="dc-sec-head">
      <div class="dc-sec-icon">⚠️</div>
      <div>
        <h2 class="dc-sec-title">PipraPay v3 ইন্টিগ্রেশনের ৪টি স্বর্ণালী নিয়ম (Crucial Rules)</h2>
      </div>
    </div>

    <div class="dc-alert">
      <strong>১. হেডারে 'S' লেটার:</strong> PipraPay v3-তে হেডারের নাম <code>MHS-PIPRAPAY-API-KEY</code>। পুরানো <code>mh-piprapay-api-key</code> দিলে "API key is invalid" এরর দেবে।<br>
      <strong>২. ডাবল /api পরিহার:</strong> বেস URL-এর শেষে <code>/api</code> থাকলে কোডে স্বয়ংক্রিয়ভাবে তা ফিল্টার করে নিতে হবে।<br>
      <strong>৩. ইমেইল ও মোবাইল বাধ্যতামূলক:</strong> পে-লোডে অবশ্যই <code>customer_email</code> ও <code>mobile_number</code> পাঠাতে হবে।<br>
      <strong>৪. URL ফিল্ড নেম:</strong> v3-তে <code>redirect_url</code>-এর পাশাপাশি <code>return_url</code> বাধ্যতামূলক।
    </div>
  </section>

  <!-- ==================== ৫. লাইসেন্স হার্ড-লক ইঞ্জিন ==================== -->
  <section class="dc-section-card" id="licenseGuard">
    <div class="dc-sec-head">
      <div class="dc-sec-icon">🔑</div>
      <div>
        <h2 class="dc-sec-title">TZ License DRM Hard-Lock (Android / Python / PHP)</h2>
      </div>
    </div>
    <p class="dc-sec-desc">
      সেন্ট্রাল সার্ভার API এন্ডপয়েন্ট: <code><?= e($apiUrl) ?></code>
    </p>

    <div class="dc-code-block">
      <div class="dc-code-bar">
        <span class="dc-file-tag">🐍 Python Bot License Guard (tz_guard.py)</span>
        <button type="button" class="dc-btn-copy" onclick="copySnippet('codePyGuard', this)">📋 কপি কোড</button>
      </div>
      <pre class="dc-pre" id="codePyGuard"><code>import requests, uuid, sys

API_URL = "<?= e($apiUrl) ?>"
APP_ID  = "tz-app-1"

def verify_license(code):
    payload = {
        "action": "activate",
        "license_code": code.strip().upper(),
        "app_id": APP_ID,
        "device_id": str(uuid.getnode()),
        "app_version": "1.0.0"
    }
    try:
        r = requests.post(API_URL, json=payload, timeout=8).json()
        if r.get("ok"):
            print("✅ লাইসেন্স অনুমোদিত!")
            return True
        print("❌ এরর:", r.get("error"))
        return False
    except Exception as e:
        print("সার্ভার এরর:", e)
        return False

if __name__ == "__main__":
    code = input("গ্লোবাল লাইসেন্স কোড দিন: ")
    if not verify_license(code):
        sys.exit(1)
    print("🚀 বট সচল হয়েছে...")</code></pre>
    </div>
  </section>

</div>

<!-- ========================================================
     ১-ক্লিক কপি ও টোস্ট নোটিফিকেশন জাভাস্ক্রিপ্ট
     ======================================================== -->
<script>
function copySnippet(elementId, btn) {
  var el = document.getElementById(elementId);
  if (!el) return;
  var text = el.innerText;

  navigator.clipboard.writeText(text).then(function() {
    var oldText = btn.innerHTML;
    btn.innerHTML = '✅ কপি হয়েছে!';
    btn.style.background = '#059669';
    btn.style.borderColor = '#059669';

    setTimeout(function() {
      btn.innerHTML = oldText;
      btn.style.background = '';
      btn.style.borderColor = '';
    }, 2000);
  }).catch(function() {
    alert('কপি ব্যর্থ হয়েছে! ম্যানুয়ালি সিলেক্ট করে কপি করুন।');
  });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
