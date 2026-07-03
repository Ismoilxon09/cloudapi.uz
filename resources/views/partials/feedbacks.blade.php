{{--
    Landing page — Feedbacks bo'limi
    Home blade'ga @include('landing.partials.feedbacks') orqali qo'shiladi
--}}

@php
    $feedbacks = \App\Models\Feedback::forLanding(12);
    $avgRating = $feedbacks->avg('rating') ?? 5;
    $totalCount = \App\Models\Feedback::where('is_published', 1)->whereNotNull('text')->where('text', '!=', '')->count();
@endphp

<section id="feedbacks" class="feedbacks-section">
  <div class="feedbacks-container">
    <div class="feedbacks-header">
      <div class="feedbacks-badge">
        <span class="material-icons-round">rate_review</span>
        Foydalanuvchilar fikri
      </div>
      <h2 class="feedbacks-title">Bizga ishonganlar so'zi</h2>
      <p class="feedbacks-subtitle">
        Real foydalanuvchilar tomonidan qoldirilgan haqiqiy fikrlar.
      </p>

      @if($totalCount > 0)
      <div class="feedbacks-stats">
        <div class="feedbacks-stat">
          <div class="feedbacks-stat-value">
            {{ number_format($avgRating, 1) }}
            <span class="material-icons-round" style="color:#FBBF24;font-size:24px;vertical-align:-4px">star</span>
          </div>
          <div class="feedbacks-stat-label">O'rtacha baho</div>
        </div>
        <div class="feedbacks-stat">
          <div class="feedbacks-stat-value">{{ $totalCount }}+</div>
          <div class="feedbacks-stat-label">Feedback</div>
        </div>
      </div>
      @endif
    </div>

    @if($feedbacks->count() > 0)
    <div class="feedbacks-grid">
      @foreach($feedbacks as $fb)
      <div class="feedback-card">
        <div class="feedback-header">
          <div class="feedback-avatar" style="background: {{ $fb->avatar_color }}">
            {{ $fb->initial }}
          </div>
          <div class="feedback-user">
            <div class="feedback-name">{{ $fb->display_name }}</div>
            <div class="feedback-date">{{ $fb->created_at->format('M Y') }}</div>
          </div>
          <div class="feedback-rating">
            @for($i = 1; $i <= 5; $i++)
              <span class="material-icons-round" style="color: {{ $i <= $fb->rating ? '#FBBF24' : 'var(--border)' }}; font-size: 15px">star</span>
            @endfor
          </div>
        </div>
        <div class="feedback-text">{{ $fb->text }}</div>
        @if($fb->admin_reply)
        <div class="feedback-reply">
          <div class="feedback-reply-header">
            <span class="material-icons-round">reply</span>
            CloudAPI jamoasidan javob
          </div>
          <div class="feedback-reply-text">{{ Str::limit($fb->admin_reply, 200) }}</div>
        </div>
        @endif
      </div>
      @endforeach
    </div>
    @endif

    {{-- Feedback yozish CTA --}}
    <div class="feedback-cta">
      <div class="feedback-cta-content">
        <h3 class="feedback-cta-title">Fikringiz biz uchun muhim</h3>
        <p class="feedback-cta-subtitle">Platforma haqidagi taassurotlaringizni yozing yoki Telegram bot orqali murojaat qiling.</p>

        <div class="feedback-cta-buttons">
          <button class="feedback-btn feedback-btn-primary" onclick="openFeedbackModal()">
            <span class="material-icons-round">edit</span>
            Fikr yozish
          </button>
          <a href="https://t.me/cloudapiuzbot" target="_blank" class="feedback-btn feedback-btn-secondary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="#229ED9">
              <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.643.135-.953l11.566-4.458c.538-.196 1.006.128.832.94z"/>
            </svg>
            Telegram orqali
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Feedback Modal --}}
<div class="feedback-modal" id="feedbackModal" onclick="if(event.target === this) closeFeedbackModal()">
  <div class="feedback-modal-content">
    <button class="feedback-modal-close" onclick="closeFeedbackModal()">
      <span class="material-icons-round">close</span>
    </button>

    <div class="feedback-modal-header">
      <h3>Fikr yozing</h3>
      <p>Sizning fikringiz platforma yaxshilanishiga yordam beradi</p>
    </div>

    <form id="feedbackForm" onsubmit="return submitFeedback(event)">
      @csrf

      @guest
      <div class="feedback-field">
        <label>Ismingiz</label>
        <input type="text" name="name" maxlength="100" placeholder="Ismingiz" class="feedback-input">
      </div>
      @endguest

      <div class="feedback-field">
        <label>Baho</label>
        <div class="rating-input" id="ratingInput">
          @for($i = 1; $i <= 5; $i++)
          <button type="button" class="rating-star" data-value="{{ $i }}" onclick="setRating({{ $i }})">
            <span class="material-icons-round">star</span>
          </button>
          @endfor
          <input type="hidden" name="rating" id="ratingValue" value="5">
        </div>
      </div>

      <div class="feedback-field">
        <label>Fikringiz</label>
        <textarea name="text" required minlength="5" maxlength="1000" rows="4" placeholder="Platforma haqida nima o'ylaysiz?" class="feedback-textarea"></textarea>
        <div class="feedback-char-count"><span id="charCount">0</span>/1000</div>
      </div>

      <div class="feedback-form-message" id="feedbackMessage"></div>

      <button type="submit" class="feedback-btn feedback-btn-primary feedback-btn-full" id="feedbackSubmitBtn">
        <span class="material-icons-round">send</span>
        Yuborish
      </button>
    </form>
  </div>
</div>

<style>
.feedbacks-section {
  padding: 100px 24px;
  background: var(--bg);
  position: relative;
  overflow: hidden;
}

.feedbacks-container {
  max-width: 1200px;
  margin: 0 auto;
}

.feedbacks-header {
  text-align: center;
  margin-bottom: 48px;
}

.feedbacks-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.feedbacks-badge .material-icons-round {
  font-size: 16px;
}

.feedbacks-title {
  font-size: 40px;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text-strong);
  margin: 0 0 12px;
  line-height: 1.15;
}

.feedbacks-subtitle {
  font-size: 16px;
  color: var(--text-muted);
  max-width: 560px;
  margin: 0 auto 24px;
  line-height: 1.5;
}

.feedbacks-stats {
  display: flex;
  gap: 40px;
  justify-content: center;
  margin-top: 20px;
}

.feedbacks-stat-value {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-strong);
  letter-spacing: -0.02em;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.feedbacks-stat-label {
  font-size: 12.5px;
  color: var(--text-muted);
  margin-top: 2px;
}

.feedbacks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 60px;
}

.feedback-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 22px;
  transition: all .2s;
  display: flex;
  flex-direction: column;
}

.feedback-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
  border-color: var(--border-strong);
}

.feedback-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.feedback-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 15px;
  flex-shrink: 0;
}

.feedback-user {
  flex: 1;
  min-width: 0;
}

.feedback-name {
  font-weight: 700;
  font-size: 13.5px;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.feedback-date {
  font-size: 11.5px;
  color: var(--text-muted);
  margin-top: 1px;
}

.feedback-rating {
  display: flex;
  gap: 1px;
}

.feedback-text {
  font-size: 13.5px;
  line-height: 1.55;
  color: var(--text);
  margin-bottom: 12px;
  flex: 1;
}

.feedback-reply {
  padding: 12px 14px;
  background: var(--bg-subtle);
  border-left: 3px solid var(--text-strong);
  border-radius: 0 8px 8px 0;
  margin-top: 12px;
}

.feedback-reply-header {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  margin-bottom: 6px;
}

.feedback-reply-header .material-icons-round {
  font-size: 14px;
}

.feedback-reply-text {
  font-size: 12.5px;
  color: var(--text);
  line-height: 1.5;
  font-style: italic;
}

.feedback-cta {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 40px;
  text-align: center;
  margin-top: 40px;
}

.feedback-cta-title {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin: 0 0 8px;
}

.feedback-cta-subtitle {
  color: var(--text-muted);
  font-size: 14px;
  margin: 0 0 24px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.feedback-cta-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.feedback-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 12px 22px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all .15s;
}

.feedback-btn .material-icons-round {
  font-size: 18px;
}

.feedback-btn-primary {
  background: var(--text-strong);
  color: var(--bg-elevated);
}

.feedback-btn-primary:hover {
  transform: translateY(-1px);
  opacity: 0.92;
}

.feedback-btn-secondary {
  background: var(--bg-subtle);
  color: var(--text-strong);
  border: 1px solid var(--border);
}

.feedback-btn-secondary:hover {
  background: var(--bg-elevated);
  border-color: var(--border-strong);
}

.feedback-btn-full {
  width: 100%;
  justify-content: center;
}

/* Modal */
.feedback-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.feedback-modal.active {
  display: flex;
  animation: fadeIn .2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.feedback-modal-content {
  background: var(--bg-elevated);
  border-radius: 16px;
  padding: 32px;
  max-width: 480px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  animation: slideUp .3s ease;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.feedback-modal-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 32px;
  height: 32px;
  border: none;
  background: var(--bg-subtle);
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
}

.feedback-modal-close:hover {
  background: var(--border);
  color: var(--text-strong);
}

.feedback-modal-header {
  margin-bottom: 24px;
}

.feedback-modal-header h3 {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 6px;
  letter-spacing: -0.02em;
}

.feedback-modal-header p {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

.feedback-field {
  margin-bottom: 18px;
}

.feedback-field label {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.feedback-input, .feedback-textarea {
  width: 100%;
  padding: 12px 14px;
  background: var(--bg-subtle);
  border: 1.5px solid var(--border);
  border-radius: 10px;
  font-size: 14px;
  color: var(--text-strong);
  font-family: inherit;
  transition: all .15s;
  resize: vertical;
}

.feedback-input:focus, .feedback-textarea:focus {
  outline: none;
  border-color: var(--text-strong);
  background: var(--bg-elevated);
}

.feedback-textarea {
  min-height: 100px;
}

.feedback-char-count {
  font-size: 11px;
  color: var(--text-subtle);
  text-align: right;
  margin-top: 4px;
}

.rating-input {
  display: flex;
  gap: 4px;
}

.rating-star {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  transition: transform .15s;
}

.rating-star:hover {
  transform: scale(1.15);
}

.rating-star .material-icons-round {
  font-size: 30px;
  color: var(--border);
  transition: color .15s;
}

.rating-star.active .material-icons-round {
  color: #FBBF24;
}

.feedback-form-message {
  display: none;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 14px;
  font-weight: 500;
}

.feedback-form-message.show {
  display: block;
}

.feedback-form-message.success {
  background: rgba(16, 185, 129, .08);
  border: 1px solid rgba(16, 185, 129, .2);
  color: #10B981;
}

.feedback-form-message.error {
  background: rgba(239, 68, 68, .08);
  border: 1px solid rgba(239, 68, 68, .2);
  color: #EF4444;
}

/* Mobile */
@media (max-width: 768px) {
  .feedbacks-section {
    padding: 60px 16px;
  }
  .feedbacks-title {
    font-size: 30px;
  }
  .feedbacks-subtitle {
    font-size: 14px;
  }
  .feedbacks-stats {
    gap: 24px;
  }
  .feedbacks-stat-value {
    font-size: 22px;
  }
  .feedbacks-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .feedback-card {
    padding: 18px;
  }
  .feedback-cta {
    padding: 28px 20px;
  }
  .feedback-cta-title {
    font-size: 22px;
  }
  .feedback-cta-buttons {
    flex-direction: column;
  }
  .feedback-btn {
    justify-content: center;
  }
  .feedback-modal-content {
    padding: 24px;
  }
}

@keyframes spinAnim {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>

<script>
let currentRating = 5;

function openFeedbackModal() {
  document.getElementById('feedbackModal').classList.add('active');
  document.body.style.overflow = 'hidden';
  setRating(5);
}

function closeFeedbackModal() {
  document.getElementById('feedbackModal').classList.remove('active');
  document.body.style.overflow = '';
}

function setRating(value) {
  currentRating = value;
  document.getElementById('ratingValue').value = value;
  document.querySelectorAll('.rating-star').forEach(star => {
    const starValue = parseInt(star.dataset.value);
    star.classList.toggle('active', starValue <= value);
  });
}

// Character counter
document.addEventListener('DOMContentLoaded', function() {
  const textarea = document.querySelector('#feedbackForm textarea[name="text"]');
  const counter = document.getElementById('charCount');
  if (textarea && counter) {
    textarea.addEventListener('input', function() {
      counter.textContent = this.value.length;
    });
  }
});

async function submitFeedback(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('feedbackSubmitBtn');
  const msg = document.getElementById('feedbackMessage');

  msg.classList.remove('show', 'success', 'error');
  btn.disabled = true;
  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<span class="material-icons-round" style="animation:spinAnim 1s linear infinite">refresh</span> Yuborilmoqda';

  const formData = new FormData(form);

  try {
    const res = await fetch('{{ route("feedback.store") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData
    });

    const data = await res.json();

    if (!res.ok || !data.ok) {
      const errors = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
      throw new Error(errors || 'Xato yuz berdi');
    }

    msg.textContent = data.message;
    msg.classList.add('show', 'success');
    form.reset();
    setRating(5);

    setTimeout(() => {
      closeFeedbackModal();
      window.location.reload();
    }, 1800);

  } catch (err) {
    msg.textContent = err.message;
    msg.classList.add('show', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }

  return false;
}
</script>