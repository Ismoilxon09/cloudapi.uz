@extends('landing.legal-layout')

@section('title', 'Xavfsizlik — CloudAPI')

@section('legal_title', 'Xavfsizlik')
@section('legal_subtitle', "CloudAPI sizning ma'lumotlaringiz va API kalitlaringizni qanday himoya qiladi")

@section('legal_content')

<div class="legal-toc">
  <div class="legal-toc-title">Bo'limlar</div>
  <ol>
    <li><a href="#overview">Umumiy ko'rinish</a></li>
    <li><a href="#encryption">Shifrlash</a></li>
    <li><a href="#api-keys">API kalitlar xavfsizligi</a></li>
    <li><a href="#auth">Autentifikatsiya</a></li>
    <li><a href="#network">Tarmoq xavfsizligi</a></li>
    <li><a href="#data">Ma'lumotlar xavfsizligi</a></li>
    <li><a href="#monitoring">Monitoring va audit</a></li>
    <li><a href="#incidents">Hodisalar bo'yicha javob</a></li>
    <li><a href="#responsible-disclosure">Zaifliklar haqida xabar</a></li>
    <li><a href="#user-tips">Foydalanuvchilar uchun maslahatlar</a></li>
  </ol>
</div>

<h2 id="overview">1. Umumiy ko'rinish</h2>

<p>CloudAPI xavfsizlikni eng yuqori prioritet sifatida ko'radi. Biz <strong>ko'p qatlamli himoya</strong> (defense in depth) yondashuvini qo'llaymiz — har bir tahdid uchun bir nechta himoya qatlami mavjud.</p>

<div class="legal-callout success">
  <strong>Asosiy printsiplar:</strong>
  <ul style="margin:8px 0 0 20px">
    <li>Minimal ma'lumot to'plash (faqat kerak bo'lganini)</li>
    <li>Eng kam imtiyozlar (least privilege)</li>
    <li>Zero trust arxitektura</li>
    <li>Doimiy monitoring</li>
    <li>Tezkor javob</li>
  </ul>
</div>

<h2 id="encryption">2. Shifrlash</h2>

<h3>2.1 Yo'lda shifrlash (in transit)</h3>
<ul>
  <li><strong>TLS 1.3</strong> barcha HTTP aloqalarda</li>
  <li><strong>HSTS</strong> (HTTP Strict Transport Security) — faqat HTTPS</li>
  <li><strong>Sertifikat:</strong> Let's Encrypt / Cloudflare avtomatik yangilanish</li>
  <li><strong>Forward secrecy</strong> kalitlari</li>
</ul>

<h3>2.2 Saqlashda shifrlash (at rest)</h3>
<ul>
  <li><strong>Parollar:</strong> bcrypt (cost factor 12) hash bilan</li>
  <li><strong>API kalitlar:</strong> SHA-256 hash (plain text hech qachon DB da emas)</li>
  <li><strong>Encrypted backup:</strong> AES-256 (kerak bo'lsa admin uchun ko'rish imkoniyati)</li>
  <li><strong>Sessiya cookie'lari:</strong> shifrlangan</li>
  <li><strong>Database:</strong> shifrlangan disk</li>
</ul>

<h2 id="api-keys">3. API kalitlar xavfsizligi</h2>

<h3>3.1 Yaratish</h3>
<ul>
  <li><strong>128-bit entropy</strong> — <code>random_bytes(16)</code> kriptografik random</li>
  <li>Format: <code>cap-{32 hex chars}</code></li>
  <li>Plain text faqat <strong>BIR MARTAGINA</strong> ko'rsatiladi</li>
</ul>

<h3>3.2 Saqlash</h3>
<ul>
  <li><strong>SHA-256 hash</strong> DB ga yoziladi</li>
  <li>Encrypted backup (admin ko'rishi mumkin, lekin shifrlangan)</li>
  <li>Plain text hech qachon log fayllarda emas</li>
</ul>

<h3>3.3 Verifikatsiya</h3>
<ul>
  <li><strong>Constant-time comparison</strong> (<code>hash_equals</code>) — timing attack himoyasi</li>
  <li><strong>Cache:</strong> 5 daqiqa (DB load kamaytirish uchun)</li>
  <li><strong>Brute-force protection:</strong> 20 noto'g'ri urinish → 10 daqiqa IP block</li>
</ul>

<h3>3.4 Boshqaruv</h3>
<ul>
  <li>Har qanday paytda <strong>revoke</strong> qilish mumkin</li>
  <li>Per-key <strong>rate limit</strong> sozlash</li>
  <li>Per-key <strong>allowed models</strong> ro'yxati</li>
  <li>Expiry date qo'yish imkoniyati</li>
</ul>

<div class="legal-callout danger">
  <strong>MUHIM:</strong> Agar siz API kalitingizni boy berdingiz deb hisoblasangiz, darhol <a href="/keys">dashboard</a> dan revoke qiling va yangi yarating.
</div>

<h2 id="auth">4. Autentifikatsiya</h2>

<h3>4.1 Parollar</h3>
<ul>
  <li>Minimal 8 belgi</li>
  <li>Katta harf, kichik harf va raqam <strong>majburiy</strong></li>
  <li>bcrypt cost factor 12</li>
  <li>Parol tarixi tekshirilmaydi (lekin siz qayta o'zgartirishingiz mumkin)</li>
</ul>

<h3>4.2 Login himoyasi</h3>
<ul>
  <li><strong>5 noto'g'ri urinish</strong> → 15 daqiqa block (per IP + email)</li>
  <li>Session fixation himoyasi (login da regenerate)</li>
  <li>Bloklangan akkauntlar darhol log out qilinadi</li>
  <li>Barcha urinishlar log qilinadi</li>
</ul>

<h3>4.3 Sessiyalar</h3>
<ul>
  <li><strong>HttpOnly</strong> cookie (JavaScript dan o'qib bo'lmaydi)</li>
  <li><strong>Secure</strong> flag (faqat HTTPS)</li>
  <li><strong>SameSite=Strict</strong> (CSRF himoyasi)</li>
  <li>Shifrlangan cookie</li>
  <li>120 daqiqa idle timeout</li>
</ul>

<h2 id="network">5. Tarmoq xavfsizligi</h2>

<h3>5.1 Rate limiting (3 darajali)</h3>
<ul>
  <li><strong>Per API key:</strong> 60 so'rov/daqiqa (sozlanadi)</li>
  <li><strong>Per IP:</strong> 300 so'rov/daqiqa (DDoS himoyasi)</li>
  <li><strong>Per user:</strong> 500 so'rov/daqiqa (multi-key abuse)</li>
</ul>

<h3>5.2 HTTP Security Headers</h3>
<ul>
  <li><code>X-Frame-Options: SAMEORIGIN</code> — clickjacking himoyasi</li>
  <li><code>X-Content-Type-Options: nosniff</code> — MIME sniffing himoyasi</li>
  <li><code>X-XSS-Protection: 1; mode=block</code></li>
  <li><code>Referrer-Policy: strict-origin-when-cross-origin</code></li>
  <li><code>Content-Security-Policy</code> — XSS asosiy himoyasi</li>
  <li><code>Strict-Transport-Security</code> — HTTPS majburiy</li>
  <li><code>Permissions-Policy</code> — kamera, mikrofon o'chirilgan</li>
</ul>

<h3>5.3 DDoS himoyasi</h3>
<ul>
  <li>Cloudflare proxy (production'da)</li>
  <li>IP-level rate limiting</li>
  <li>Geo-blocking imkoniyati</li>
  <li>Bot tahlil qilish</li>
</ul>

<h2 id="data">6. Ma'lumotlar xavfsizligi</h2>

<h3>6.1 SQL Injection himoyasi</h3>
<ul>
  <li>Faqat <strong>parametrlangan so'rovlar</strong> (Eloquent ORM)</li>
  <li>Hech qachon raw SQL user input bilan birga emas</li>
  <li>Input validatsiya har bir maydon uchun</li>
</ul>

<h3>6.2 XSS (Cross-Site Scripting)</h3>
<ul>
  <li>Barcha output Blade <code>@{{ }}</code> avtomatik escape</li>
  <li>Ism kabi maydonlar uchun regex validatsiya (faqat harflar)</li>
  <li>strip_tags() qo'shimcha himoya</li>
  <li>CSP header</li>
</ul>

<h3>6.3 CSRF (Cross-Site Request Forgery)</h3>
<ul>
  <li>Laravel CSRF token barcha POST so'rovlarda</li>
  <li>SameSite=Strict cookie</li>
  <li>API'lar uchun Bearer token (CSRF kerak emas)</li>
</ul>

<h3>6.4 Mass Assignment</h3>
<ul>
  <li>Qattiq <code>$fillable</code> ro'yxat</li>
  <li><code>role</code>, <code>status</code>, <code>balance</code> user input'dan kelmaydi</li>
  <li>Validatsiya har bir maydon uchun</li>
</ul>

<h3>6.5 File Upload</h3>
<ul>
  <li>Real MIME tekshirish (extension emas, file content)</li>
  <li>Faqat ruxsat etilgan formatlar (JPG, PNG)</li>
  <li>5MB hajm cheklov</li>
  <li>Random filename (path traversal himoyasi)</li>
  <li>Dimensions cheklov (max 4000×4000)</li>
</ul>

<h2 id="monitoring">7. Monitoring va audit</h2>

<h3>7.1 Loglash</h3>
<ul>
  <li><strong>Security log</strong> — barcha shubhali harakatlar (90 kun)</li>
  <li><strong>Payment log</strong> — barcha to'lov harakatlari (365 kun)</li>
  <li><strong>API log</strong> — har bir API so'rovi (30 kun)</li>
  <li><strong>Audit log</strong> — har bir admin harakati</li>
</ul>

<h3>7.2 Real-time monitoring</h3>
<ul>
  <li>Anomal trafik aniqlash</li>
  <li>Brute-force urinishlar haqida darhol xabar</li>
  <li>Server resurslari (CPU, RAM, disk)</li>
  <li>Database performance</li>
</ul>

<h2 id="incidents">8. Hodisalar bo'yicha javob</h2>

<p>Xavfsizlik hodisasi sodir bo'lganda:</p>

<ol>
  <li><strong>1 soat ichida</strong> — hodisa aniqlanadi va to'xtatiladi</li>
  <li><strong>24 soat ichida</strong> — affected userlarga xabar yuboriladi</li>
  <li><strong>7 kun ichida</strong> — to'liq tahlil va incident report</li>
  <li><strong>Kerak bo'lsa</strong> — qonun ijro etuvchi organlarga xabar</li>
</ol>

<h2 id="responsible-disclosure">9. Zaifliklar haqida xabar (Responsible Disclosure)</h2>

<p>Agar siz xavfsizlik zaifligini topgan bo'lsangiz, biz sizdan <strong>responsible disclosure</strong> printsiplariga rioya qilishingizni so'raymiz:</p>

<ol>
  <li>Zaiflikni omma oldida e'lon qilmang</li>
  <li>Bizga xususiy ravishda yozing: <a href="mailto:security@cloudapi.uz">security@cloudapi.uz</a></li>
  <li>Bizga zaiflikni tuzatish uchun vaqt bering (odatda 90 kun)</li>
  <li>Boshqa foydalanuvchilarga zarar yetkazmang</li>
</ol>

<div class="legal-callout success">
  <strong>Bug bounty:</strong> Biz hozircha rasmiy bug bounty dasturi yo'q, lekin muhim zaifliklarni topganlar uchun rahmat sifatida xizmatda kredit beramiz va sizni Hall of Fame ga qo'shamiz.
</div>

<h2 id="user-tips">10. Foydalanuvchilar uchun maslahatlar</h2>

<p>O'z xavfsizligingizni ta'minlash uchun:</p>

<h3>Parol</h3>
<ul>
  <li>Kuchli parol ishlating (kamida 12 belgi, har xil belgilar)</li>
  <li>Boshqa saytlardagi parolingizni qayta ishlatmang</li>
  <li>Password manager ishlating (1Password, Bitwarden)</li>
</ul>

<h3>API kalitlar</h3>
<ul>
  <li><strong>Hech qachon</strong> public repository (GitHub) ga qo'ymang</li>
  <li>Environment variables (.env) da saqlang</li>
  <li>Har bir loyiha uchun alohida kalit yarating</li>
  <li>Rate limit va allowed models sozlang</li>
  <li>Mavjud emas keldarsiz revoke qiling</li>
</ul>

<h3>Akkaunt</h3>
<ul>
  <li>Email ga kirish imkoniyatini himoya qiling (Gmail 2FA yoqing)</li>
  <li>Shubhali harakatlarni darhol bizga xabar bering</li>
  <li>Public Wi-Fi'da login qilmang (yoki VPN ishlating)</li>
</ul>

<div class="legal-callout">
  <strong>Yana savol bormi?</strong> Xavfsizlik bo'yicha har qanday savol uchun bizga yozing: <a href="mailto:security@cloudapi.uz">security@cloudapi.uz</a>
</div>

@endsection