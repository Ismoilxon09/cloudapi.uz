<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kirish bloklangan — CloudAPI</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background: #0a0a0a;
  color: #e5e5e5;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.container {
  max-width: 520px;
  width: 100%;
  background: #141414;
  border: 1px solid #262626;
  border-radius: 16px;
  padding: 40px 36px;
  text-align: center;
  box-shadow: 0 24px 60px rgba(0,0,0,0.4);
}
.icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 22px;
  background: rgba(239, 68, 68, 0.1);
  border: 2px solid rgba(239, 68, 68, 0.3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #EF4444;
  font-size: 36px;
}
h1 {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 10px;
  color: #fff;
}
.subtitle {
  font-size: 14px;
  color: #a3a3a3;
  margin-bottom: 26px;
  line-height: 1.55;
}
.details {
  background: #0a0a0a;
  border: 1px solid #262626;
  border-radius: 10px;
  padding: 16px 18px;
  margin-bottom: 22px;
  text-align: left;
}
.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 7px 0;
  font-size: 13px;
}
.detail-row + .detail-row { border-top: 1px solid #262626; }
.detail-key { color: #737373; }
.detail-value {
  color: #fff;
  font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
}
.actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 24px;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 11px 20px;
  border-radius: 9px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  transition: all .15s;
  cursor: pointer;
  border: none;
}
.btn-primary {
  background: #fff;
  color: #0a0a0a;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(255,255,255,.1); }
.btn-secondary {
  background: #1f1f1f;
  color: #e5e5e5;
  border: 1px solid #404040;
}
.btn-secondary:hover { background: #262626; }
.footer {
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid #262626;
  font-size: 12px;
  color: #737373;
  line-height: 1.6;
}
.footer a { color: #e5e5e5; text-decoration: none; font-weight: 600; }
.footer a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
  <div class="icon">⛔</div>
  <h1>Kirish bloklangan</h1>
  <p class="subtitle">
    Sizning IP manzilingiz CloudAPI'ga kirish uchun bloklangan.
    Shubhali harakat yoki qoidalarni buzish aniqlandi.
  </p>

  <div class="details">
    <div class="detail-row">
      <span class="detail-key">IP manzil</span>
      <span class="detail-value">{{ $ip }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-key">Sabab</span>
      <span class="detail-value" style="text-align:right;max-width:60%">{{ $reason }}</span>
    </div>
    @if($is_permanent)
    <div class="detail-row">
      <span class="detail-key">Holat</span>
      <span class="detail-value" style="color:#EF4444">Doimiy blok</span>
    </div>
    @elseif($remaining)
    <div class="detail-row">
      <span class="detail-key">Bloklangan vaqt</span>
      <span class="detail-value">{{ $remaining }}</span>
    </div>
    @endif
  </div>

  <p class="subtitle" style="margin-bottom:0;font-size:13px">
    Agar bu xato deb hisoblasangiz yoki blokni olib tashlash kerak bo'lsa,
    quyidagilar orqali murojaat qiling.
  </p>

  <div class="actions">
    <a href="https://t.me/cloudapiuzbot" class="btn btn-primary" target="_blank">
      💬 Telegram bot
    </a>
    <a href="https://t.me/cloudapinews" class="btn btn-secondary" target="_blank">
      📢 Yangiliklar kanali
    </a>
    <a href="https://t.me/coder_nurmatov" class="btn btn-secondary" target="_blank">
      Support orqali murojaat
    </a>
  </div>

  <div class="footer">
    Email orqali murojaat: <a href="mailto:support@cloudapi.uz">support@cloudapi.uz</a>
    <br>
    Murojaat qilganda IP manzilingiz va sababini ko'rsating.
  </div>
</div>
</body>
</html>