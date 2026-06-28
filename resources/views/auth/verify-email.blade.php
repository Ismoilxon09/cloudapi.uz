<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Emailingizni tasdiqlang</title>
</head>
<body style="margin:0;padding:0;background:#F8FAFC;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F8FAFC;padding:40px 20px;">
  <tr>
    <td align="center">

      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);">

        <!-- Header with logo -->
        <tr>
          <td style="padding:32px 40px;border-bottom:1px solid #E2E8F0;">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background:#0F172A;width:36px;height:36px;border-radius:9px;text-align:center;vertical-align:middle;padding:7px;">
                  <img src="{{ config('app.url') }}/brand/logo-mark-white.svg" alt="CloudAPI" width="22" height="18" style="display:block;margin:0 auto;">
                </td>
                <td style="padding-left:12px;font-size:18px;font-weight:800;color:#0F172A;letter-spacing:-0.02em;vertical-align:middle;">cloud<span style="font-weight:500;color:#64748B;">api</span></td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h1 style="margin:0 0 12px;font-size:24px;font-weight:800;color:#0F172A;letter-spacing:-0.02em;">
              Salom, {{ $user->name }}!
            </h1>

            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
              CloudAPI'ga ro'yxatdan o'tganingiz uchun rahmat! Akkauntingizdan to'liq foydalanish uchun emailingizni tasdiqlashingiz kerak.
            </p>

            <p style="margin:0 0 28px;font-size:15px;line-height:1.6;color:#475569;">
              Quyidagi tugmani bosing:
            </p>

            <!-- CTA Button -->
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
              <tr>
                <td style="border-radius:10px;background:#0F172A;">
                  <a href="{{ $verifyUrl }}" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#FFFFFF;text-decoration:none;border-radius:10px;">
                    Emailni tasdiqlash
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#94A3B8;">
              Yoki quyidagi linkni nusxalab brauzeringizga joylang:
            </p>

            <p style="margin:0 0 28px;font-size:12px;line-height:1.5;color:#475569;background:#F1F5F9;padding:12px;border-radius:8px;font-family:'JetBrains Mono',monospace;word-break:break-all;">
              {{ $verifyUrl }}
            </p>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #E2E8F0;padding-top:24px;">
              <tr>
                <td style="font-size:13px;line-height:1.6;color:#94A3B8;">
                  <strong style="color:#475569;">⏱ Bu link 24 soat davomida amal qiladi.</strong><br>
                  Agar bu sizning so'rovingiz bo'lmasa, bu xatni e'tiborsiz qoldiring.
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:24px 40px;background:#F8FAFC;border-top:1px solid #E2E8F0;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:#94A3B8;">
              CloudAPI — O'zbekiston uchun AI API platforma
            </p>
            <p style="margin:0;font-size:12px;color:#94A3B8;">
              <a href="{{ config('app.url') }}" style="color:#3B82F6;text-decoration:none;">cloudapi.uz</a> ·
              <a href="https://t.me/coder_nurmatov" style="color:#3B82F6;text-decoration:none;">Telegram</a> ·
              <a href="mailto:support@cloudapi.uz" style="color:#3B82F6;text-decoration:none;">Support</a>
            </p>
          </td>
        </tr>
      </table>

      <p style="margin:24px 0 0;font-size:11px;color:#CBD5E1;">
        Bu avtomatik xat. Bu emailga javob bermang.
      </p>

    </td>
  </tr>
</table>

</body>
</html>