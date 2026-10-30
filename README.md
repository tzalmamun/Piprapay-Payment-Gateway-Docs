# Piprapay-Payment-Gateway-Docs

# 💳 PipraPay v3 Payment Gateway — Developer Integration Docs

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Payment Gateway](https://img.shields.io/badge/Gateway-PipraPay%20v3-orange.svg)](https://payment.tzalmamun.com)
[![Status](https://img.shields.io/badge/Status-Tested%20%26%20Working-green.svg)](#)

PipraPay v3 (Self-Hosted / Hosted) অটোমেটিক পেমেন্ট গেটওয়ে ইন্টিগ্রেশনের সম্পূর্ণ টেকনিক্যাল গাইডলাইন, ইউনিভার্সাল AI মাস্টার প্রম্পট, ডাটাবেজ স্কিমা এবং পিএইচপি কোড।

---

## 📌 সূচিপত্র (Table of Contents)
1. [৪টি স্বর্ণালী নিয়ম (Crucial Rules)](#-৪টি-স্বর্ণালী-নিয়ম-crucial-rules)
2. [🤖 ইউনিভার্সাল AI মাস্টার প্রম্পট](#-ইউনিভার্সাল-ai-মাস্টার-প্রম্পট-master-prompt)
3. [🗄️ ডাটাবেস টেবিল স্কিমা (MySQL Schema)](#️-ডাটাবেস-টেবিল-স্কিমা-mysql-schema)
4. [💳 সোর্স কোড আর্কিটেকচার](#-সোর্স-কোড-আর্কিটেকচার)
   - [১. pay.php (পেমেন্ট তৈরি ও রিডাইরেক্ট)](#১-payphp-পেমেন্ট-ইনিশিয়েট-ও-রিডাইরেক্ট)
   - [২. pay-success.php (ভেরিফাই ও ব্যালেন্স/এক্সেস আনলক)](#২-pay-successphp-ভেরিফাই-ও-এক্সেস-আনলক)
   - [৩. api/piprapay-webhook.php (সার্ভার-টু-সার্ভার ওয়েবহুক)](#৩-apipiprapay-webhookphp-সার্ভার-ওয়েবহুক)
5. [⚠️ কমন এরর ও সমাধান (Troubleshooting)](#️-কমন-এরর-ও-সমাধান-troubleshooting)

---

## ⚡ ৪টি স্বর্ণালী নিয়ম (Crucial Rules)

1. **হেডারে 'S' লেটার আবশ্যক:** PipraPay v3-তে হেডারের নাম অবশ্যই `MHS-PIPRAPAY-API-KEY` হতে হবে। পুরনো `mh-piprapay-api-key` দিলে `API key is invalid` এরর আসবে। (সেফটির জন্য কোডে দুটিই পাঠানো ভালো)।
2. **ডাবল `/api` প্রতিরোধ:** বেস ইউআরএল-এর শেষে `/api` থাকলে তা কোডে ফিল্টার করতে হবে:
   ```php
   $cleanHost = rtrim(preg_replace('#/api/?$#i', '', $rawBaseUrl), '/');
