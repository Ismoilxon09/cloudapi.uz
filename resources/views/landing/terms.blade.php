@extends('landing.legal-layout')

@section('title', "Foydalanish shartlari — CloudAPI")

@section('legal_title', "Foydalanish shartlari")
@section('legal_subtitle', "CloudAPI xizmatidan foydalanish qoidalari va shartlari")

@section('legal_content')

<div class="legal-toc">
  <div class="legal-toc-title">Bo'limlar</div>
  <ol>
    <li><a href="#accept">Shartlarni qabul qilish</a></li>
    <li><a href="#service">Xizmat tavsifi</a></li>
    <li><a href="#account">Akkaunt va ro'yxatdan o'tish</a></li>
    <li><a href="#payment">To'lov va balans</a></li>
    <li><a href="#usage">Foydalanish qoidalari</a></li>
    <li><a href="#prohibited">Taqiqlangan harakatlar</a></li>
    <li><a href="#limits">Cheklovlar va limitlar</a></li>
    <li><a href="#liability">Mas'uliyat</a></li>
    <li><a href="#termination">Xizmatni tugatish</a></li>
    <li><a href="#changes">O'zgarishlar va aloqa</a></li>
  </ol>
</div>

<h2 id="accept">1. Shartlarni qabul qilish</h2>

<p>CloudAPI (cloudapi.uz) xizmatidan foydalanish orqali siz ushbu Foydalanish shartlariga rozilik bildirgan bo'lasiz. Agar siz shartlardan biriga rozi bo'lmasangiz, xizmatdan foydalanmang.</p>

<p>Ushbu shartlar O'zbekiston Respublikasi qonunchiligiga muvofiq tuzilgan.</p>

<h2 id="service">2. Xizmat tavsifi</h2>

<p><strong>CloudAPI</strong> — bu O'zbekistonda joylashgan AI API proxy xizmati. Biz dunyodagi turli AI provider'larga (OpenAI, Anthropic, Google va boshqalar) qulay yo'lda ulanish imkonini beramiz.</p>

<p>Xizmatimiz quyidagilarni o'z ichiga oladi:</p>
<ul>
  <li>300+ AI modeliga yagona API orqali kirish</li>
  <li>So'mda to'lov (USD konvertatsiyasi avtomatik)</li>
  <li>Real-time monitoring va statistika</li>
  <li>API kalitlar boshqaruvi</li>
  <li>Playground (test interfeysi)</li>
  <li>3 tilda ishlash (O'zbek, Ingliz, Rus)</li>
</ul>

<h2 id="account">3. Akkaunt va ro'yxatdan o'tish</h2>

<h3>3.1 Talablar</h3>
<ul>
  <li>Siz 18 yoshdan katta bo'lishingiz kerak</li>
  <li>To'g'ri va aniq ma'lumotlar berishingiz kerak</li>
  <li>Bir kishi faqat bitta akkaunt yarata oladi</li>
</ul>

<h3>3.2 Akkaunt xavfsizligi</h3>
<ul>
  <li>Kuchli parol o'rnatasiz</li>
  <li>API kalitlaringizni hech kim bilan baham ko'rmaysiz</li>
  <li>Akkaunt orqali sodir bo'lgan barcha harakatlar uchun mas'ulsiz</li>
  <li>Shubhali harakatlarni darhol bizga xabar berasiz</li>
</ul>

<h2 id="payment">4. To'lov va balans</h2>

<h3>4.1 Narxlash</h3>
<ul>
  <li><strong>Pay-as-you-go</strong> — faqat ishlatganingiz uchun to'laysiz</li>
  <li>Narxlar OpenRouter narxi + <strong>30% marja</strong></li>
  <li>USD → UZS konvertatsiyasi joriy kurs bo'yicha (har kuni yangilanadi)</li>
  <li>Bepul modellar — to'lovsiz, kunlik limit bilan</li>
</ul>

<h3>4.2 To'lov usullari</h3>
<ul>
  <li>Manual to'lov (karta orqali screenshot bilan) — hozircha</li>
  <li>Payme, Click — yaqin kelajakda</li>
</ul>

<h3>4.3 Balansni to'ldirish</h3>
<ul>
  <li>Minimal to'ldirish: 10,000 so'm</li>
  <li>Maksimal to'ldirish: 50,000,000 so'm (bir martada)</li>
  <li>To'lov tasdiqlanishi: odatda 1-24 soat ichida (manual usul uchun)</li>
  <li>Tasdiqlangandan keyin Telegram orqali xabar yuboriladi</li>
</ul>

<h3>4.4 Qaytarish (refund)</h3>
<div class="legal-callout warning">
  <strong>Balansda qolgan pul qaytarib berilmaydi</strong> — bu API foydalanish uchun mo'ljallangan. Lekin agar bizning tomondan xato bo'lsa (ortiqcha hisoblangan, xizmat ishlamagan), to'liq qaytarish qilamiz.
</div>

<h3>4.5 Bonuslar</h3>
<ul>
  <li>Ro'yxatdan o'tish bonusi: 5,000 so'm (sozlanadi)</li>
  <li>Referral bonus: 10,000 so'm har bir tomonga</li>
  <li>Bonus mablag'lari faqat API ishlatish uchun, qaytarib berilmaydi</li>
</ul>

<h2 id="usage">5. Foydalanish qoidalari</h2>

<p>Siz xizmatdan foydalanganda quyidagilarga rioya qilasiz:</p>

<ul>
  <li>API kalitlarni xavfsiz saqlash</li>
  <li>Rate limit'larga rioya qilish</li>
  <li>Qonuniy maqsadlar uchun ishlatish</li>
  <li>Boshqa foydalanuvchilarga zarar yetkazmaslik</li>
  <li>Xizmatga zarar yetkazmaslik</li>
  <li>Qonuniylikni tekshirish — sizning mamlakatingizda AI ishlatish qonuniyligi</li>
</ul>

<h2 id="prohibited">6. Taqiqlangan harakatlar</h2>

<div class="legal-callout danger">
  <strong>Quyidagi harakatlar QAT'IY TAQIQLANADI:</strong>
</div>

<h3>6.1 Texnik buzilishlar</h3>
<ul>
  <li>Tizimga ruxsatsiz kirishga urinish</li>
  <li>Reverse engineering, scraping</li>
  <li>Rate limit'ni aylanib o'tishga urinish</li>
  <li>Multiple akkauntlar yaratish (referral abuse)</li>
  <li>DDoS yoki boshqa hujumlar</li>
  <li>API kalitlarni boshqa kishilarga sotish</li>
</ul>

<h3>6.2 Noqonuniy kontent yaratish</h3>
<ul>
  <li>O'zbekiston Respublikasi qonunchiligiga zid kontent</li>
  <li>Bolalar zo'ravonligi (CSAM)</li>
  <li>Zo'ravonlikka chaqirish, terrorizm</li>
  <li>Firibgarlik (phishing, scam)</li>
  <li>Spam yoki kiber tahdid</li>
  <li>Mualliflik huquqlarini buzuvchi kontent</li>
</ul>

<h3>6.3 Boshqa</h3>
<ul>
  <li>Boshqa kishilar nomidan akkaunt yaratish</li>
  <li>Bizning brendimizdan ruxsatsiz foydalanish</li>
  <li>Xizmatimizdan kichik raqobatchi xizmat yaratish</li>
</ul>

<h3>6.4 Buzilish oqibatlari</h3>
<p>Qoidalarni buzgan akkauntlar:</p>
<ul>
  <li>Vaqtinchalik bloklash (1-30 kun)</li>
  <li>Doimiy bloklash</li>
  <li>Balans pul qaytarilmaydi</li>
  <li>Qonun ijro etuvchi organlarga xabar berish (jiddiy hollarda)</li>
</ul>

<h2 id="limits">7. Cheklovlar va limitlar</h2>

<h3>7.1 Rate limits</h3>
<ul>
  <li>Default: 60 so'rov/daqiqa (per API kalit)</li>
  <li>Sozlanadigan (Pro foydalanuvchilar uchun)</li>
  <li>IP-level: 300 so'rov/daqiqa</li>
</ul>

<h3>7.2 API kalitlar</h3>
<ul>
  <li>Maksimal 10 ta kalit har bir akkaunt uchun (sozlanadi)</li>
</ul>

<h3>7.3 Bepul tier</h3>
<ul>
  <li>Bepul modellar — 100 so'rov/kun</li>
  <li>Ratelimit pasayadi</li>
</ul>

<h2 id="liability">8. Mas'uliyat va kafolat</h2>

<h3>8.1 Uptime</h3>
<p>Biz <strong>99.5% uptime</strong> ga harakat qilamiz, lekin <strong>kafolat bermaymiz</strong>. Texnik nosozliklar, server muammolari, OpenRouter va boshqa provider'lar tomondan kelib chiqqan to'xtashlar uchun biz mas'ul emasmiz.</p>

<h3>8.2 AI javoblar uchun</h3>
<div class="legal-callout warning">
  <strong>MUHIM:</strong> AI tomonidan yaratilgan kontent uchun biz mas'ul emasmiz. AI noto'g'ri, xato yoki noaniq ma'lumot berishi mumkin. Muhim qarorlar uchun AI javobini ko'r-ko'rona ishlatmang.
</div>

<h3>8.3 Mas'uliyat cheklovi</h3>
<p>Hech qanday vaziyatda CloudAPI quyidagilar uchun mas'ul bo'lmaydi:</p>
<ul>
  <li>Yo'qotilgan foyda yoki biznes imkoniyatlari</li>
  <li>AI tomonidan berilgan noto'g'ri javoblar</li>
  <li>Sizning kodingizdagi xatolar</li>
  <li>Uchinchi tomon xizmatlari (OpenRouter va h.k.) muammolari</li>
  <li>Forsh majeure holatlari</li>
</ul>

<p>Bizning maksimal mas'uliyatimiz oxirgi 30 kun ichida to'lagan summangiz bilan cheklangan.</p>

<h2 id="termination">9. Xizmatni tugatish</h2>

<h3>9.1 Sizning tomondan</h3>
<p>Siz har qanday paytda akkauntingizni o'chirib tashlashingiz mumkin. Buning uchun <a href="mailto:support@cloudapi.uz">support@cloudapi.uz</a> ga yozing. Balansdagi pul qaytarilmaydi.</p>

<h3>9.2 Bizning tomondan</h3>
<p>Biz quyidagi hollarda akkauntingizni cheklash yoki o'chirish huquqimiz bor:</p>
<ul>
  <li>Ushbu shartlarni buzgan bo'lsangiz</li>
  <li>Qonunbuzarlik faoliyat aniqlansa</li>
  <li>30 kundan ortiq faoliyatsizlik</li>
  <li>Texnik sabablar</li>
</ul>

<p>Bunday hollarda biz sizga 7 kun oldindan email orqali xabar beramiz (jiddiy buzilishdan tashqari).</p>

<h2 id="changes">10. O'zgarishlar va aloqa</h2>

<h3>10.1 Shartlarda o'zgarishlar</h3>
<p>Biz ushbu shartlarni vaqti-vaqti bilan yangilashimiz mumkin. Muhim o'zgarishlar haqida sizga email yoki Telegram orqali xabar beriladi. Davom etib foydalanishingiz yangi shartlarga rozilik bildiradi.</p>

<h3>10.2 Aloqa</h3>
<ul>
  <li><strong>Umumiy savollar:</strong> <a href="mailto:support@cloudapi.uz">support@cloudapi.uz</a></li>
  <li><strong>Telegram:</strong> <a href="https://t.me/coder_nurmatov" target="_blank">@coder_nurmatov</a></li>
  <li><strong>Xavfsizlik:</strong> <a href="mailto:security@cloudapi.uz">security@cloudapi.uz</a></li>
</ul>

<h3>10.3 Qonun va yurisdiksiya</h3>
<p>Ushbu shartlar <strong>O'zbekiston Respublikasi</strong> qonunchiligiga muvofiq tuzilgan va talqin qilinadi. Har qanday nizolar Toshkent shahar sudlarida ko'riladi.</p>

<div class="legal-callout success">
  Ushbu shartlarni o'qiganingiz uchun rahmat. Agar savollar bo'lsa — bizga yozing!
</div>

@endsection