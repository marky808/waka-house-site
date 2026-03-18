<?php
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
</head>
<body>

  <!-- Header -->
  <header class="header scrolled">
    <div class="container">
      <div class="header-logo">
        <a href="index.html">
          <span class="logo-mark">W</span>
          <span>
            株式会社WAKA
            <span class="logo-sub">いえサポ</span>
          </span>
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

          <p style="font-size: 0.85rem; color: #666; text-align: center; margin-bottom: 24px;">
            <a href="privacy.html" style="color: var(--color-primary); text-decoration: underline;">プライバシーポリシー</a>に同意の上、送信してください。
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

  <div class="fixed-cta">
    <a href="https://line.me/" class="cta-line" target="_blank" rel="noopener">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 5.82 2 10.5c0 4.21 3.74 7.74 8.78 8.4.34.07.8.23.92.52.1.27.07.68.03.95l-.15.9c-.04.27-.2 1.05.92.57s6.13-3.61 8.36-6.18C22.63 13.59 22 12.11 22 10.5 22 5.82 17.52 2 12 2z"/></svg>
      LINE
    </a>
    <a href="tel:050-8892-0110" class="cta-tel">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.81.36 1.6.68 2.34a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.74.32 1.53.55 2.34.68A2 2 0 0122 16.92z"/></svg>
      電話
    </a>
    <a href="contact.php" class="cta-mail">&#9993; お問合せ</a>
  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
