@extends('admin.layout')

@section('title', "To'lov #{$tx->id}")
@section('page_title', "To'lov #{$tx->id}")

@push('styles')
<style>
.pay-show { padding: 24px; max-width: 1200px; margin: 0 auto; }

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 16px;
}

.back-link:hover { color: var(--text-strong); }
.back-link .material-icons-round { font-size: 16px; }

.show-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
}

@media (max-width: 1000px) { .show-grid { grid-template-columns: 1fr; } }

.detail-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
}

.detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.detail-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-strong);
}

.amount-big {
  font-size: 40px;
  font-weight: 800;
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-strong);
  letter-spacing: -0.02em;
  margin-bottom: 6px;
}

.amount-big .currency {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-muted);
  margin-left: 6px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
}

.detail-row:last-child { border-bottom: none; }
.detail-key { color: var(--text-muted); }
.detail-value { color: var(--text-strong); font-weight: 600; font-family: 'JetBrains Mono', monospace; }

.receipt-card {
  margin-top: 16px;
}

.receipt-img {
  width: 100%;
  max-width: 400px;
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  transition: transform .2s;
}

.receipt-img:hover { transform: scale(1.02); }

.no-receipt {
  padding: 40px;
  text-align: center;
  color: var(--text-muted);
  background: var(--bg-subtle);
  border-radius: 10px;
  font-size: 13px;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  background: var(--bg-subtle);
  border-radius: 10px;
  margin-bottom: 16px;
}

.user-card-avatar {
  width: 48px; height: 48px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 700;
}

.user-card-info { flex: 1; min-width: 0; }
.user-card-name { font-size: 15px; font-weight: 700; color: var(--text-strong); }
.user-card-email { font-size: 12px; color: var(--text-muted); }

.balance-info {
  padding: 12px;
  background: var(--bg-subtle);
  border-radius: 10px;
  margin-top: 12px;
}

.balance-row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 12px;
}

.balance-label { color: var(--text-muted); }
.balance-value { font-weight: 700; font-family: 'JetBrains Mono', monospace; }

/* Action card */
.action-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
  position: sticky;
  top: 80px;
}

.action-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.action-subtitle {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 16px;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action-form { display: none; }
.action-form.active { display: block; }

.action-divider {
  height: 1px;
  background: var(--border);
  margin: 14px 0;
}

.status-result {
  padding: 14px;
  border-radius: 10px;
  text-align: center;
}

.status-result.completed {
  background: rgba(16,185,129,.1);
  border: 1px solid rgba(16,185,129,.3);
  color: var(--success);
}

.status-result.failed {
  background: rgba(239,68,68,.1);
  border: 1px solid rgba(239,68,68,.3);
  color: var(--danger);
}

.status-result .material-icons-round {
  font-size: 32px;
  margin-bottom: 6px;
}

.status-result-title { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
.status-result-meta { font-size: 11px; opacity: 0.8; }

.history-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 12px;
}

.history-item + .history-item { margin-top: 2px; }
.history-item:hover { background: var(--bg-subtle); }

.history-icon {
  width: 24px; height: 24px;
  border-radius: 6px;
  background: var(--bg-subtle);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.history-icon .material-icons-round { font-size: 12px; }
.history-icon.deposit { background: rgba(16,185,129,.12); color: var(--success); }
.history-icon.usage { background: var(--bg-subtle); color: var(--text-muted); }

.history-info { flex: 1; min-width: 0; }
.history-desc { font-weight: 500; color: var(--text-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.history-date { font-size: 10px; color: var(--text-subtle); }
.history-amount { font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 11px; }
.history-amount.positive { color: var(--success); }
.history-amount.negative { color: var(--text-muted); }

/* Image preview modal */
.img-modal {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.9);
  z-index: 200;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.img-modal.open { display: flex; }
.img-modal img { max-width: 100%; max-height: 100%; object-fit: contain; }

.img-modal-close {
  position: absolute;
  top: 20px; right: 20px;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: rgba(255,255,255,.1);
  color: white;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="pay-show">
  <a href="{{ route('admin.payments.index') }}" class="back-link">
    <span class="material-icons-round">arrow_back</span>
    To'lovlar ro'yxatiga
  </a>

  <div class="show-grid">
    <!-- Main -->
    <div>
      <!-- Detail card -->
      <div class="detail-card">
        <div class="detail-header">
          <div>
            <div class="amount-big">
              +{{ number_format($tx->amount_uzs, 0, '.', ' ') }}
              <span class="currency">so'm</span>
            </div>
            <div style="font-size:12px;color:var(--text-muted)">Manual to'lov · TX #{{ $tx->id }}</div>
          </div>

          @if($tx->status === 'pending')
            <span class="status-badge status-pending" style="padding:5px 12px;font-size:11px">
              <span class="material-icons-round">hourglass_top</span>
              Kutilmoqda
            </span>
          @elseif($tx->status === 'completed')
            <span class="status-badge status-completed" style="padding:5px 12px;font-size:11px">
              <span class="material-icons-round">check_circle</span>
              Tasdiqlangan
            </span>
          @else
            <span class="status-badge status-failed" style="padding:5px 12px;font-size:11px">
              <span class="material-icons-round">cancel</span>
              Rad etilgan
            </span>
          @endif
        </div>

        <div class="detail-row">
          <span class="detail-key">Yaratilgan</span>
          <span class="detail-value">{{ $tx->created_at->format('M d, Y · H:i:s') }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-key">To'lov usuli</span>
          <span class="detail-value">Manual transfer</span>
        </div>
        <div class="detail-row">
          <span class="detail-key">Status</span>
          <span class="detail-value">{{ ucfirst($tx->status) }}</span>
        </div>
        @if($tx->reviewed_at)
        <div class="detail-row">
          <span class="detail-key">Ko'rib chiqilgan</span>
          <span class="detail-value">{{ $tx->reviewed_at->format('M d, Y · H:i') }}</span>
        </div>
        @endif
        @if($tx->admin_note)
        <div class="detail-row">
          <span class="detail-key">Admin izohi</span>
          <span class="detail-value" style="font-family:Inter;font-weight:500">{{ $tx->admin_note }}</span>
        </div>
        @endif
        @if($tx->rejection_reason)
        <div class="detail-row">
          <span class="detail-key">Rad etish sababi</span>
          <span class="detail-value" style="font-family:Inter;font-weight:500;color:var(--danger)">{{ $tx->rejection_reason }}</span>
        </div>
        @endif
      </div>

      <!-- Receipt -->
      <div class="detail-card">
        <div class="detail-header" style="margin-bottom:16px;padding-bottom:0;border:none">
          <div class="detail-title">Chek (screenshot)</div>
        </div>

        @if($tx->reference)
          <img src="{{ asset('storage/' . $tx->reference) }}"
               class="receipt-img"
               onclick="openImgModal(this.src)"
               alt="Receipt">
        @else
          <div class="no-receipt">
            <span class="material-icons-round" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.5">image_not_supported</span>
            Chek yuklanmagan
          </div>
        @endif
      </div>

      <!-- User history -->
      @if($userHistory->isNotEmpty())
      <div class="detail-card">
        <div class="detail-header" style="margin-bottom:14px;padding-bottom:0;border:none">
          <div class="detail-title">Userning oxirgi tranzaksiyalari</div>
        </div>

        @foreach($userHistory as $h)
        <div class="history-item">
          <div class="history-icon {{ $h->type }}">
            <span class="material-icons-round">
              @if($h->type === 'deposit') arrow_downward
              @elseif($h->type === 'usage') data_usage
              @else circle
              @endif
            </span>
          </div>
          <div class="history-info">
            <div class="history-desc">{{ $h->description ?? ucfirst($h->type) }}</div>
            <div class="history-date">{{ $h->created_at->format('M d, H:i') }}</div>
          </div>
          <div class="history-amount {{ $h->amount_uzs > 0 ? 'positive' : 'negative' }}">
            {{ $h->amount_uzs > 0 ? '+' : '' }}{{ number_format($h->amount_uzs, 0, '.', ' ') }}
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>

    <!-- Sidebar -->
    <div>
      <!-- User info -->
      <div class="detail-card">
        <div class="user-card">
          <div class="user-card-avatar">{{ strtoupper(substr($tx->user->name ?? '?', 0, 1)) }}</div>
          <div class="user-card-info">
            <div class="user-card-name">{{ $tx->user->name ?? 'Unknown' }}</div>
            <div class="user-card-email">{{ $tx->user->email ?? '—' }}</div>
          </div>
        </div>

        <a href="{{ route('admin.users.show', $tx->user) }}" class="btn btn-secondary w-full" style="margin-bottom:8px">
          <span class="material-icons-round">person</span>
          User profil
        </a>

        <div class="balance-info">
          <div class="balance-row">
            <span class="balance-label">Hozirgi balans</span>
            <span class="balance-value">{{ number_format($tx->user->wallet?->balance_uzs ?? 0, 0, '.', ' ') }} so'm</span>
          </div>
          <div class="balance-row">
            <span class="balance-label">Jami to'ldirilgan</span>
            <span class="balance-value">{{ number_format($tx->user->wallet?->total_deposited ?? 0, 0, '.', ' ') }} so'm</span>
          </div>
          <div class="balance-row">
            <span class="balance-label">Sarflangan</span>
            <span class="balance-value">{{ number_format($tx->user->wallet?->total_spent ?? 0, 0, '.', ' ') }} so'm</span>
          </div>
        </div>
      </div>

      <!-- Action card -->
      <div class="action-card">
        @if($tx->status === 'pending')
          <div class="action-title">Tasdiqlash kerakmi?</div>
          <div class="action-subtitle">Tasdiqlash → balansga avtomatik qo'shiladi va userga xabar boradi.</div>

          <div class="action-buttons" id="defaultButtons">
            <button class="btn btn-success" onclick="showForm('approve')">
              <span class="material-icons-round">check_circle</span>
              Tasdiqlash
            </button>
            <button class="btn btn-danger" onclick="showForm('reject')">
              <span class="material-icons-round">cancel</span>
              Rad etish
            </button>
          </div>

          <!-- Approve form -->
          <form action="{{ route('admin.payments.approve', $tx) }}" method="POST" class="action-form" id="approveForm">
            @csrf
            <div class="field">
              <label class="label">Izoh (ixtiyoriy)</label>
              <textarea name="admin_note" class="input" rows="3" placeholder="Masalan: 8600 karta orqali kelgan..."></textarea>
            </div>
            <div style="display:flex;gap:8px">
              <button type="button" class="btn btn-secondary" onclick="cancelForm()">Bekor qilish</button>
              <button type="submit" class="btn btn-success" style="flex:1">
                <span class="material-icons-round">check</span>
                Tasdiqlash
              </button>
            </div>
          </form>

          <!-- Reject form -->
          <form action="{{ route('admin.payments.reject', $tx) }}" method="POST" class="action-form" id="rejectForm">
            @csrf
            <div class="field">
              <label class="label">Rad etish sababi <span style="color:var(--danger)">*</span></label>
              <textarea name="rejection_reason" class="input" rows="3" placeholder="Masalan: Chek topilmadi, miqdor mos kelmadi..." required></textarea>
            </div>
            <div style="display:flex;gap:8px">
              <button type="button" class="btn btn-secondary" onclick="cancelForm()">Bekor qilish</button>
              <button type="submit" class="btn btn-danger" style="flex:1">
                <span class="material-icons-round">close</span>
                Rad etish
              </button>
            </div>
          </form>
        @elseif($tx->status === 'completed')
          <div class="status-result completed">
            <span class="material-icons-round">check_circle</span>
            <div class="status-result-title">Tasdiqlangan</div>
            <div class="status-result-meta">{{ $tx->reviewed_at?->format('M d, H:i') }}</div>
          </div>
        @else
          <div class="status-result failed">
            <span class="material-icons-round">cancel</span>
            <div class="status-result-title">Rad etilgan</div>
            <div class="status-result-meta">{{ $tx->reviewed_at?->format('M d, H:i') }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Image modal -->
<div class="img-modal" id="imgModal" onclick="closeImgModal()">
  <button class="img-modal-close"><span class="material-icons-round">close</span></button>
  <img id="imgModalSrc" src="" alt="Receipt">
</div>

<script>
function showForm(type) {
  document.getElementById('defaultButtons').style.display = 'none';
  document.getElementById('approveForm').classList.toggle('active', type === 'approve');
  document.getElementById('rejectForm').classList.toggle('active', type === 'reject');
}

function cancelForm() {
  document.getElementById('defaultButtons').style.display = 'flex';
  document.querySelectorAll('.action-form').forEach(f => f.classList.remove('active'));
}

function openImgModal(src) {
  document.getElementById('imgModalSrc').src = src;
  document.getElementById('imgModal').classList.add('open');
}

function closeImgModal() {
  document.getElementById('imgModal').classList.remove('open');
}
</script>
@endsection