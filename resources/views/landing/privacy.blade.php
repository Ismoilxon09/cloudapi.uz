@extends('landing.legal-layout')

@section('title', 'Maxfiylik siyosati — CloudAPI')

@section('legal_title', 'Maxfiylik siyosati')
@section('legal_subtitle', "CloudAPI sizning ma'lumotlaringizni qanday yig'adi, ishlatadi va himoya qiladi")

@section('legal_content')

<div class="legal-toc">
  <div class="legal-toc-title">Bo'limlar</div>
  <ol>
    <li><a href="#intro">Kirish</a></li>
    <li><a href="#collect">Qanday ma'lumotlar yig'iladi</a></li>
    <li><a href="#use">Ma'lumotlardan qanday foydalanamiz</a></li>
    <li><a href="#share">Ma'lumotlarni baham ko'rish</a></li>
    <li><a href="#storage">Ma'lumotlar saqlash muddati</a></li>
    <li><a href="#security">Xavfsizlik choralari</a></li>
    <li><a href="#rights">Sizning huquqlaringiz</a></li>
    <li><a href="#cookies">Cookie va kuzatuv</a></li>
    <li><a href="#children">Bolalar bilan ishlash</a></li>
    <li><a href="#changes">O'zgarishlar</a></li>
  </ol>
</div>

<h2 id="intro">1. Kirish</h2>

<p><strong>CloudAPI</strong> (keyingi o'rinlarda — "biz", "Bizning xizmat") sizning shaxsiy ma'lumotlaringizni himoya qilishni o'z burchimiz deb biladi. Ushbu maxfiylik siyosati cloudapi.uz va u bilan bog'liq xizmatlardan foydalanish chog'ida ma'lumotlaringiz qanday yig'ilishi va ishlatilishini tushuntiradi.</p>

<p>Xizmatimizdan foydalanish orqali siz ushbu siyosatning shartlariga rozilik bildirgan bo'lasiz.</p>

<h2 id="collect">2. Qanday ma'lumotlar yig'iladi</h2>

<h3>2.1 Siz beradigan ma'lumotlar</h3>
<ul>
  <li><strong>Akkaunt ma'lumotlari:</strong> ism, email, parol (shifrlangan holda), telefon raqami (ixtiyoriy), mamlakat</li>
  <li><strong>To'lov ma'lumotlari:</strong> to'lov chek screenshotlari (manual to'lov uchun)</li>
  <li><strong>API foydalanish:</strong> AI modellariga yuborgan so'rovlaringiz va olgan javoblaringiz</li>
  <li><strong>Aloqa ma'lumotlari:</strong> support bilan yozishmalar</li>
</ul>

<h3>2.2 Avtomatik to'planadigan ma'lumotlar</h3>
<ul>
  <li><strong>Texnik ma'lumotlar:</strong> IP manzil, brauzer turi, qurilma ma'lumotlari, operatsion tizim</li>
  <li><strong>Foydalanish ma'lumotlari:</strong> qaysi sahifalarni ko'rganingiz, qancha vaqt sarflaganingiz</li>
  <li><strong>API so'rov tarixi:</strong> qaysi modeldan, qachon, qancha token ishlatilganligi</li>
  <li><strong>Cookie:</strong> sessiya ma'lumotlari va sozlamalar</li>
</ul>

<h3>2.3 Uchinchi tomonlardan</h3>
<p>Agar siz Telegram orqali tizimga kirsangiz, biz Telegram'dan ID, username va ismingizni olamiz.</p>

<h2 id="use">3. Ma'lumotlardan qanday foydalanamiz</h2>

<p>Yig'ilgan ma'lumotlardan quyidagi maqsadlar uchun foydalanamiz:</p>
<ul>
  <li>Xizmat ko'rsatish va akkauntingizni boshqarish</li>
  <li>To'lovlarni qayta ishlash va tasdiqlash</li>
  <li>API so'rovlaringizni OpenRouter va boshqa provider'larga yuborish</li>
  <li>Texnik yordam ko'rsatish</li>
  <li>Foydalanish statistikasini ko'rsatish</li>
  <li>Xavfsizlikni ta'minlash (firibgarlik va hujumlarni aniqlash)</li>
  <li>Xizmatimizni yaxshilash va yangi xususiyatlar qo'shish</li>
  <li>Muhim bildirishnomalar yuborish (balans kam qolganda, to'lov tasdiqlanganda)</li>
</ul>

<div class="legal-callout success">
  <strong>✓ Biz nima qilmaymiz:</strong>
  <ul style="margin:8px 0 0 20px">
    <li>Sizning AI so'rovlaringizni o'qimaymiz yoki tahlil qilmaymiz</li>
    <li>Ma'lumotlaringizni reklama uchun ishlatmaymiz</li>
    <li>Uchinchi tomonlarga sotmaymiz</li>
    <li>Spam yubormaymiz</li>
  </ul>
</div>

<h2 id="share">4. Ma'lumotlarni baham ko'rish</h2>

<p>Biz sizning ma'lumotlaringizni quyidagi hollardagina baham ko'ramiz:</p>

<h3>4.1 Xizmat ko'rsatuvchilar bilan</h3>
<ul>
  <li><strong>OpenRouter</strong> va boshqa AI provider'lar — API so'rovlaringizni qayta ishlash uchun (so'rovning matn qismi yuboriladi, lekin shaxsingiz emas)</li>
  <li><strong>Telegram</strong> — bildirishnomalar va bot integratsiya uchun (faqat siz ulanganida)</li>
</ul>

<h3>4.2 Qonun talabi bo'yicha</h3>
<p>O'zbekiston Respublikasi qonunchiligiga muvofiq, vakolatli organlar talabi asosida ma'lumotlarni taqdim etishimiz mumkin.</p>

<h3>4.3 Xavfsizlik maqsadida</h3>
<p>Firibgarlik, hujum yoki noqonuniy faoliyatga shubha bo'lganda, tergov uchun ma'lumotlarni baham ko'rishimiz mumkin.</p>

<h2 id="storage">5. Ma'lumotlar saqlash muddati</h2>

<ul>
  <li><strong>Akkaunt ma'lumotlari:</strong> akkauntingiz faol bo'lguncha</li>
  <li><strong>API so'rov tarixi:</strong> 90 kun (statistika uchun)</li>
  <li><strong>To'lov tarixi:</strong> 5 yil (soliq qonunchiligi talabi)</li>
  <li><strong>Xavfsizlik loglari:</strong> 90 kun</li>
  <li><strong>Server loglari:</strong> 30 kun</li>
</ul>

<p>Akkauntingizni o'chirishni so'rasangiz, 30 kun ichida barcha shaxsiy ma'lumotlaringiz tizimdan o'chiriladi (qonun talab qilmagan ma'lumotlardan tashqari).</p>

<h2 id="security">6. Xavfsizlik choralari</h2>

<p>Biz sizning ma'lumotlaringizni himoya qilish uchun quyidagi choralarni ko'ramiz:</p>
<ul>
  <li><strong>Shifrlash:</strong> HTTPS/TLS 1.3 barcha aloqalarda</li>
  <li><strong>Parol himoyasi:</strong> bcrypt algoritmi bilan parollar hash qilinadi</li>
  <li><strong>API kalitlar:</strong> SHA-256 hash holda saqlanadi (plain text DB ga yozilmaydi)</li>
  <li><strong>Rate limiting:</strong> hujumlardan himoya (3 darajali: per-key, per-IP, per-user)</li>
  <li><strong>Brute-force himoya:</strong> 5 ta noto'g'ri urinishdan keyin vaqtinchalik blokirovka</li>
  <li><strong>CSRF va XSS himoyasi:</strong> barcha so'rovlar uchun</li>
  <li><strong>Audit log:</strong> har bir muhim harakat qayd qilinadi</li>
  <li><strong>Server xavfsizligi:</strong> firewall, OS yangilanishlari, monitoring</li>
</ul>

<p>Batafsil: <a href="{{ route('security') }}">Xavfsizlik siyosatimizni ko'ring</a></p>

<h2 id="rights">7. Sizning huquqlaringiz</h2>

<p>O'zbekiston Respublikasining "Shaxsiy ma'lumotlar to'g'risida"gi Qonuniga muvofiq, sizda quyidagi huquqlar bor:</p>

<ul>
  <li><strong>Kirish huquqi:</strong> qanday ma'lumotlar yig'ilganligini bilish</li>
  <li><strong>Tuzatish huquqi:</strong> noto'g'ri ma'lumotlarni o'zgartirish</li>
  <li><strong>O'chirish huquqi:</strong> akkauntingizni o'chirib tashlash</li>
  <li><strong>Eksport huquqi:</strong> ma'lumotlaringizni mashina o'qiy oladigan formatda olish</li>
  <li><strong>Ruxsatni qaytarib olish huquqi:</strong> har qanday paytda</li>
  <li><strong>Shikoyat qilish huquqi:</strong> vakolatli organlarga murojaat qilish</li>
</ul>

<p>Bu huquqlaringizni amalga oshirish uchun <a href="mailto:support@cloudapi.uz">support@cloudapi.uz</a> ga yozing.</p>

<h2 id="cookies">8. Cookie va kuzatuv</h2>

<p>Biz quyidagi cookie'lardan foydalanamiz:</p>

<ul>
  <li><strong>Sessiya cookie:</strong> tizimga kirgan holatda saqlash uchun (majburiy)</li>
  <li><strong>Til sozlamasi:</strong> tanlangan tilni eslab qolish</li>
  <li><strong>Mavzu:</strong> dark/light mode tanlovingiz</li>
  <li><strong>CSRF token:</strong> xavfsizlik uchun</li>
</ul>

<div class="legal-callout">
  Biz <strong>uchinchi tomon kuzatuv kukilarini ishlatmaymiz</strong> (Google Analytics, Facebook Pixel va h.k. yo'q). Sizning faoliyatingiz faqat o'z serverlarimizda qayd qilinadi.
</div>

<h2 id="children">9. Bolalar bilan ishlash</h2>

<p>CloudAPI 18 yoshdan kichik shaxslar uchun mo'ljallanmagan. Biz bilib turib 18 yoshdan kichiklarning shaxsiy ma'lumotlarini yig'maymiz. Agar shunday holat ma'lum bo'lsa, biz darhol akkauntni o'chirib tashlaymiz.</p>

<h2 id="changes">10. O'zgarishlar</h2>

<p>Ushbu maxfiylik siyosati vaqti-vaqti bilan yangilanishi mumkin. Muhim o'zgarishlar haqida sizga email yoki Telegram orqali xabar beriladi. Davom etib foydalanishingiz yangi siyosatga rozilik bildiradi.</p>

<div class="legal-callout warning">
  <strong>Eslatma:</strong> Ushbu siyosat O'zbekiston Respublikasi qonunchiligiga muvofiq tuzilgan. Boshqa yurisdiksiyalarda qo'shimcha huquqlar bo'lishi mumkin.
</div>

@endsection