<?php
// reCAPTCHA v3 設定
define('RECAPTCHA_SITE_KEY', '6Lfq5I4sAAAAAJuHwjVza5RuuS5IFr8UWO7myxGD');
define('RECAPTCHA_SECRET_KEY', '6Lfq5I4sAAAAACXcsqicU18fDCKctMxBd7XZ5Veq');

// 送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF簡易対策
    session_start();
    if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_POST['token'] !== $_SESSION['csrf_token']) {
        die('不正なリクエストです。');
    }

    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // バリデーション
    $errors = [];
    if ($name === '') $errors[] = 'お名前を入力してください。';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = '正しいメールアドレスを入力してください。';
    if ($message === '') $errors[] = 'お問合せ内容を入力してください。';

    // reCAPTCHA v3 検証
    if (empty($errors)) {
        $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        if ($recaptcha_token === '') {
            $errors[] = 'reCAPTCHA認証に失敗しました。ページを再読み込みしてください。';
        } else {
            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
            $verify_data = http_build_query([
                'secret'   => RECAPTCHA_SECRET_KEY,
                'response' => $recaptcha_token,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ]);
            $verify_opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $verify_data,
                ],
            ];
            $verify_context = stream_context_create($verify_opts);
            $verify_response = file_get_contents($verify_url, false, $verify_context);
            $recaptcha_result = json_decode($verify_response, true);

            if (!$recaptcha_result['success'] || $recaptcha_result['score'] < 0.5) {
                $errors[] = 'スパム判定されました。お手数ですがお電話にてご連絡ください。';
            }
        }
    }

    if (empty($errors)) {
        $to      = 'info@waka-house.co.jp';
        $subject = '【いえサポ】お問合せがありました';
        $body    = "以下の内容でお問合せがありました。\n\n";
        $body   .= "お名前: {$name}\n";
        $body   .= "メールアドレス: {$email}\n";
        $body   .= "電話番号: {$phone}\n";
        $body   .= "お問合せ内容:\n{$message}\n";

        $headers = "From: {$email}\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mb_language('Japanese');
        mb_internal_encoding('UTF-8');
        $result = mb_send_mail($to, $subject, $body, $headers);

        if ($result) {
            header('Location: thanks.html');
            exit;
        } else {
            $errors[] = '送信に失敗しました。お手数ですがお電話にてご連絡ください。';
        }
    }
}

// CSRFトークン生成
if (!isset($_SESSION)) session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お問合せ | 株式会社WAKA</title>
  <meta name="description" content="株式会社WAKAへのお問合せページです。家づくりに関するご相談はお気軽にお問合せください。">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
</head>
<body>

  <!-- Header -->
  <header class="header scrolled">
    <div class="container">
      <div class="header-logo">
        <a href="index.html">
          <img src="assets/images/logo.png" alt="株式会社WAKA" class="header-logo-img">
        </a>
      </div>
      <nav class="nav-desktop">
        <a href="iesapo.html">いえサポとは</a>
        <a href="support.html">一括サポート</a>
        <a href="company.html">会社概要</a>
        <a href="contact.php" class="nav-cta">お問合せ</a>
      </nav>
      <button class="hamburger" aria-label="メニュー">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>

  <div class="nav-overlay"></div>
  <nav class="nav-mobile">
    <a href="index.html">ホーム</a>
    <a href="iesapo.html">いえサポとは</a>
    <a href="support.html">一括サポート</a>
    <a href="company.html">会社概要</a>
    <a href="contact.php">お問合せ</a>
    <a href="privacy.html">プライバシーポリシー</a>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <span class="en">Contact</span>
      <h1>お問合せ</h1>
    </div>
  </div>

  <!-- Contact Form -->
  <section class="section">
    <div class="container">
      <div class="fade-in" style="text-align: center; margin-bottom: 40px;">
        <img src="https://cdn.undraw.co/illustration/message-sent_iyz6.svg" alt="お問い合わせ" class="illust illust-lg" style="max-width: 280px; margin: 0 auto;">
      </div>
      <div class="service-intro fade-in" style="margin-bottom: 48px;">
        <p>家づくりに関するご質問・ご相談はお気軽にお問合せください。<br>担当者より折り返しご連絡いたします。</p>
      </div>

      <?php if (!empty($errors)): ?>
      <div style="max-width: 640px; margin: 0 auto 32px; padding: 20px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px;">
        <?php foreach ($errors as $err): ?>
          <p style="color: #dc2626; font-size: 0.9rem; margin-bottom: 4px;"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="contact-form fade-in">
        <form action="contact.php" method="POST">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

          <div class="form-group">
            <label>お名前 <span class="required">必須</span></label>
            <input type="text" name="name" placeholder="山田 太郎" value="<?php echo isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
          </div>

          <div class="form-group">
            <label>メールアドレス <span class="required">必須</span></label>
            <input type="email" name="email" placeholder="example@email.com" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
          </div>

          <div class="form-group">
            <label>電話番号</label>
            <input type="tel" name="phone" placeholder="090-1234-5678" value="<?php echo isset($phone) ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : ''; ?>">
          </div>

          <div class="form-group">
            <label>お問合せ内容 <span class="required">必須</span></label>
            <textarea name="message" placeholder="ご相談内容をご記入ください" required><?php echo isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
          </div>

          <p style="font-size: 0.85rem; color: #666; text-align: center; margin-bottom: 16px;">
            <a href="privacy.html" style="color: var(--color-primary); text-decoration: underline;">プライバシーポリシー</a>に同意の上、送信してください。
          </p>
          <p style="font-size: 0.72rem; color: #999; text-align: center; margin-bottom: 24px;">
            このサイトはreCAPTCHA v3で保護されています。Googleの<a href="https://policies.google.com/privacy" target="_blank" rel="noopener" style="color: #999; text-decoration: underline;">プライバシーポリシー</a>と<a href="https://policies.google.com/terms" target="_blank" rel="noopener" style="color: #999; text-decoration: underline;">利用規約</a>が適用されます。
          </p>

          <div class="form-submit">
            <button type="submit">送信する &#8594;</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo">株式会社WAKA</div>
          <p>〒160-0023<br>東京都新宿区西新宿3-3-13<br>西新宿水間ビル2F</p>
          <div class="footer-sns">
            <a href="https://www.instagram.com/waka_iesapo/" target="_blank" rel="noopener" aria-label="Instagram">
              <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <a href="https://lin.ee/kRTWrUI" target="_blank" rel="noopener" aria-label="LINE">
              <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 5.82 2 10.5c0 4.21 3.74 7.74 8.78 8.4.34.07.8.23.92.52.1.27.07.68.03.95l-.15.9c-.04.27-.2 1.05.92.57s6.13-3.61 8.36-6.18C22.63 13.59 22 12.11 22 10.5 22 5.82 17.52 2 12 2z"/></svg>
            </a>
          </div>
        </div>
        <div class="footer-nav">
          <h4>ページ</h4>
          <a href="index.html">ホーム</a>
          <a href="iesapo.html">いえサポとは</a>
          <a href="support.html">一括サポート</a>
          <a href="company.html">会社概要</a>
          <a href="contact.php">お問合せ</a>
        </div>
        <div class="footer-contact">
          <h4>お問合せ</h4>
          <p class="tel">050-8892-0110</p>
          <p>info@waka-house.co.jp</p>
        </div>
      </div>
      <div class="footer-bottom">
        <a href="privacy.html">プライバシーポリシー</a> &nbsp;|&nbsp;
        <span>&copy; 2024 株式会社WAKA All Rights Reserved.</span>
      </div>
    </div>
  </footer>

  <!-- Floating CTA (PC) -->
  <div class="floating-cta">
    <a href="https://lin.ee/kRTWrUI" class="float-line" target="_blank" rel="noopener">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 5.82 2 10.5c0 4.21 3.74 7.74 8.78 8.4.34.07.8.23.92.52.1.27.07.68.03.95l-.15.9c-.04.27-.2 1.05.92.57s6.13-3.61 8.36-6.18C22.63 13.59 22 12.11 22 10.5 22 5.82 17.52 2 12 2z"/></svg>
      LINEで相談
    </a>
    <a href="tel:050-8892-0110" class="float-tel">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.81.36 1.6.68 2.34a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.74.32 1.53.55 2.34.68A2 2 0 0122 16.92z"/></svg>
      電話する
    </a>
  </div>

  <!-- Fixed CTA (Mobile) -->
  <div class="fixed-cta-mobile">
    <a href="https://lin.ee/kRTWrUI" class="cta-line" target="_blank" rel="noopener">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 5.82 2 10.5c0 4.21 3.74 7.74 8.78 8.4.34.07.8.23.92.52.1.27.07.68.03.95l-.15.9c-.04.27-.2 1.05.92.57s6.13-3.61 8.36-6.18C22.63 13.59 22 12.11 22 10.5 22 5.82 17.52 2 12 2z"/></svg>
      LINE
    </a>
    <a href="tel:050-8892-0110" class="cta-tel">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.81.36 1.6.68 2.34a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.74.32 1.53.55 2.34.68A2 2 0 0122 16.92z"/></svg>
      電話
    </a>
  </div>

  <script src="assets/js/main.js"></script>
  <script>
    document.querySelector('.contact-form form').addEventListener('submit', function(e) {
      e.preventDefault();
      var form = this;
      grecaptcha.ready(function() {
        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'contact'}).then(function(token) {
          document.getElementById('g-recaptcha-response').value = token;
          form.submit();
        });
      });
    });
  </script>
</body>
</html>
