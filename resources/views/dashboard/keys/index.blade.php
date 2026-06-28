@extends('layouts.app')

@section('title', __('keys.title') . ' — CloudAPI')

@push('styles')
<style>
.keys-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 24px;
}

.keys-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.keys-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.keys-subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

.new-key-alert {
  background: var(--bg-elevated);
  border: 1px solid var(--success);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.new-key-alert-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
}

.new-key-alert-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(16, 185, 129, .1);
  color: var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
}

.new-key-alert-icon .material-icons-round { font-size: 18px; }

.new-key-alert-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
}

.new-key-alert-subtitle {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 2px;
}

.new-key-display {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 12px 16px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  color: var(--text-strong);
  display: flex;
  align-items: center;
  gap: 10px;
  word-break: break-all;
}

.new-key-copy {
  margin-left: auto;
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.new-key-copy:hover { color: var(--text-strong); border-color: var(--border-strong); }
.new-key-copy .material-icons-round { font-size: 16px; }

/* Empty state */
.empty-state {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 80px 24px;
  text-align: center;
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.empty-state-icon .material-icons-round {
  font-size: 32px;
  color: var(--text-subtle);
}

.empty-state h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 6px;
}

.empty-state p {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 20px;
}

/* Keys table */
.keys-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
}

.keys-table {
  width: 100%;
  border-collapse: collapse;
}

.keys-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  background: var(--bg-subtle);
}

.keys-table td {
  padding: 16px 18px;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
}

.keys-table tr:last-child td { border-bottom: none; }
.keys-table tr:hover td { background: var(--bg-subtle); }

.key-name {
  font-weight: 600;
  color: var(--text-strong);
}

.key-value {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-muted);
}

.key-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 99px;
}

.key-status .material-icons-round { font-size: 10px; }

.key-status.active { background: rgba(16, 185, 129, .1); color: var(--success); }
.key-status.paused { background: rgba(245, 158, 11, .1); color: var(--warning); }
.key-status.revoked { background: rgba(239, 68, 68, .1); color: var(--danger); }

.key-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}

.key-action-btn {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}

.key-action-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.key-action-btn.danger:hover {
  background: rgba(239, 68, 68, .1);
  border-color: var(--danger);
  color: var(--danger);
}

.key-action-btn .material-icons-round { font-size: 16px; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .5);
  z-index: 100;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.modal-overlay.open { display: flex; }

.modal {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 28px;
  width: 100%;
  max-width: 480px;
  box-shadow: var(--shadow-lg);
  animation: modalIn .25s var(--ease-spring) both;
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 20px;
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 20px;
}

/* ============ NEW KEY MODAL (OpenRouter style) ============ */
.modal-new-key {
  max-width: 560px;
  padding: 36px 32px;
}

.new-key-icon-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.new-key-success-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(16, 185, 129, .12);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: successPop .4s var(--ease-spring) both;
}

.new-key-success-icon .material-icons-round {
  color: var(--success, #10B981);
  font-size: 32px;
  font-weight: 700;
}

@keyframes successPop {
  from { transform: scale(0); opacity: 0; }
  60% { transform: scale(1.1); }
  to { transform: scale(1); opacity: 1; }
}

.new-key-modal-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-strong);
  text-align: center;
  margin: 0 0 6px;
  letter-spacing: -0.02em;
}

.new-key-modal-subtitle {
  font-size: 14px;
  color: var(--text-muted);
  text-align: center;
  margin: 0 0 24px;
  line-height: 1.5;
}

.new-key-warning-box {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  background: rgba(245, 158, 11, .08);
  border: 1px solid rgba(245, 158, 11, .25);
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 20px;
  font-size: 13px;
  line-height: 1.5;
  color: var(--text);
}

.new-key-warning-box .material-icons-round {
  color: #F59E0B;
  font-size: 20px;
  flex-shrink: 0;
}

.new-key-warning-box strong { color: var(--text-strong); }

.new-key-value-box {
  margin-bottom: 20px;
}

.new-key-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.new-key-value-display {
  display: flex;
  align-items: stretch;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
}

.new-key-value-display code {
  flex: 1;
  padding: 14px 16px;
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-strong);
  background: transparent;
  word-break: break-all;
  overflow-x: auto;
  white-space: nowrap;
}

.new-key-copy-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 18px;
  background: var(--text-strong);
  color: var(--bg-elevated);
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
  transition: all .15s;
  border-left: 1px solid var(--border);
}

.new-key-copy-btn .material-icons-round { font-size: 17px; }
.new-key-copy-btn:hover { opacity: 0.9; }
.new-key-copy-btn.copied { background: var(--success, #10B981); }

.new-key-tips {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 24px;
}

.new-key-tip {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 12px 14px;
  background: var(--bg-subtle);
  border-radius: 9px;
  font-size: 12.5px;
  line-height: 1.5;
  color: var(--text);
}

.new-key-tip .material-icons-round {
  color: var(--accent, #2563EB);
  font-size: 18px;
  flex-shrink: 0;
}

.new-key-tip code {
  background: var(--bg);
  padding: 1px 6px;
  border-radius: 4px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 11.5px;
  color: var(--text-strong);
}

.new-key-tip strong { color: var(--text-strong); }

.new-key-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}

.new-key-actions .btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.new-key-actions .material-icons-round { font-size: 17px; }

/* Mobile */
@media (max-width: 600px) {
  .modal-new-key { padding: 24px 18px; }
  .new-key-value-display { flex-direction: column; }
  .new-key-copy-btn { 
    padding: 10px; 
    border-left: none; 
    border-top: 1px solid var(--border);
    justify-content: center;
  }
  .new-key-actions { flex-direction: column-reverse; }
  .new-key-actions .btn { width: 100%; justify-content: center; }
}
</style>
@endpush

@section('content')

<div class="keys-page">
  <div class="keys-header">
    <div>
      <h1 class="keys-title">{{ __('keys.title') }}</h1>
      <p class="keys-subtitle">{{ __('keys.subtitle') }}</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">
      <span class="material-icons-round">add</span>
      {{ __('keys.new_key') }}
    </button>
  </div>

  @if(session('new_key'))
  {{-- OpenRouter-style modal — yaratilgan kalitni ko'rsatish --}}
  <div class="modal-overlay open" id="newKeyModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal modal-new-key">
      <div class="new-key-icon-wrap">
        <div class="new-key-success-icon">
          <span class="material-icons-round">check</span>
        </div>
      </div>

      <h2 class="new-key-modal-title">{{ __('keys.created_success') }}</h2>
      <p class="new-key-modal-subtitle">
        {{ __('keys.created_warning') }}
      </p>

      <div class="new-key-warning-box">
        <span class="material-icons-round">warning</span>
        <div>
          <strong>Diqqat!</strong> Bu kalit faqat <strong>bir marta</strong> ko'rsatiladi.
          Modalni yopgandan keyin uni qaytadan ko'ra olmaysiz.
        </div>
      </div>

      <div class="new-key-value-box">
        <div class="new-key-label">Sizning yangi API kalitingiz:</div>
        <div class="new-key-value-display">
          <code id="newKeyValue">{{ session('new_key') }}</code>
          <button class="new-key-copy-btn" id="copyKeyBtn" onclick="copyNewKey()">
            <span class="material-icons-round">content_copy</span>
            <span class="copy-text">Nusxalash</span>
          </button>
        </div>
      </div>

      <div class="new-key-tips">
        <div class="new-key-tip">
          <span class="material-icons-round">lightbulb</span>
          <div>
            <strong>Maslahat:</strong> Kalitni xavfsiz joyda saqlang
            (masalan <code>.env</code> faylida yoki password manager'da)
          </div>
        </div>
        <div class="new-key-tip">
          <span class="material-icons-round">security</span>
          <div>
            <strong>Xavfsizlik:</strong> Kalitni hech kim bilan baham ko'rmang.
            Kalit yo'qolsa darhol uni bekor qiling va yangisini yarating.
          </div>
        </div>
      </div>

      <div class="new-key-actions">
        <button class="btn btn-secondary" onclick="document.getElementById('newKeyModal').classList.remove('open')">
          Yopish
        </button>
        <button class="btn btn-primary" onclick="copyNewKeyAndClose()">
          <span class="material-icons-round">content_copy</span>
          Nusxalash va yopish
        </button>
      </div>
    </div>
  </div>

  <script>
    function copyNewKey() {
      const key = document.getElementById('newKeyValue').textContent.trim();
      navigator.clipboard.writeText(key).then(() => {
        const btn = document.getElementById('copyKeyBtn');
        btn.classList.add('copied');
        btn.querySelector('.material-icons-round').textContent = 'check';
        btn.querySelector('.copy-text').textContent = 'Nusxalandi!';
        setTimeout(() => {
          btn.classList.remove('copied');
          btn.querySelector('.material-icons-round').textContent = 'content_copy';
          btn.querySelector('.copy-text').textContent = 'Nusxalash';
        }, 2000);
      });
    }

    function copyNewKeyAndClose() {
      copyNewKey();
      setTimeout(() => {
        document.getElementById('newKeyModal').classList.remove('open');
      }, 800);
    }
  </script>
  @endif

  @if($keys->count() === 0)
    <div class="empty-state">
      <div class="empty-state-icon">
        <span class="material-icons-round">key_off</span>
      </div>
      <h3>{{ __('keys.no_keys') }}</h3>
      <p>{{ __('keys.no_keys_desc') }}</p>
      <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">
        <span class="material-icons-round">add</span>
        {{ __('keys.create') }}
      </button>
    </div>
  @else
    <div class="keys-card">
      <table class="keys-table">
        <thead>
          <tr>
            <th>{{ __('keys.name') }}</th>
            <th>{{ __('keys.key') }}</th>
            <th>{{ __('keys.requests') }}</th>
            <th>{{ __('keys.last_used') }}</th>
            <th>{{ __('keys.status') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($keys as $key)
          <tr>
            <td><span class="key-name">{{ $key->name }}</span></td>
            <td><span class="key-value">{{ $key->key_prefix }}</span></td>
            <td>{{ number_format($key->total_requests) }}</td>
            <td>
              @if($key->last_used_at)
                <span style="color:var(--text-muted)">{{ $key->last_used_at->diffForHumans() }}</span>
              @else
                <span style="color:var(--text-subtle)">{{ __('keys.never') }}</span>
              @endif
            </td>
            <td>
              @if($key->status === 'active')
                <span class="key-status active">
                  <span class="material-icons-round">circle</span>
                  {{ __('common.active') }}
                </span>
              @elseif($key->status === 'paused')
                <span class="key-status paused">
                  <span class="material-icons-round">pause</span>
                  {{ __('common.paused') }}
                </span>
              @else
                <span class="key-status revoked">
                  <span class="material-icons-round">block</span>
                  {{ __('common.revoked') }}
                </span>
              @endif
            </td>
            <td>
              <div class="key-actions">
                @if($key->status === 'active')
                <form action="{{ route('keys.revoke', $key) }}" method="POST" onsubmit="return confirm('{{ __('keys.actions.confirm_revoke') }}')" style="margin:0">
                  @csrf
                  <button type="submit" class="key-action-btn" title="{{ __('keys.actions.revoke') }}">
                    <span class="material-icons-round">block</span>
                  </button>
                </form>
                @endif
                <form action="{{ route('keys.destroy', $key) }}" method="POST" onsubmit="return confirm('{{ __('keys.actions.confirm_delete') }}')" style="margin:0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="key-action-btn danger" title="{{ __('keys.actions.delete') }}">
                    <span class="material-icons-round">delete_outline</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="createModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">{{ __('keys.create_modal.title') }}</div>
    <form action="{{ route('keys.store') }}" method="POST">
      @csrf
      <div class="field">
        <label class="label">{{ __('keys.create_modal.name') }}</label>
        <input type="text" name="name" class="input" placeholder="{{ __('keys.create_modal.name_placeholder') }}" required autofocus>
        <div class="help-text">{{ __('keys.create_modal.help') }}</div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').classList.remove('open')">
          {{ __('keys.create_modal.cancel') }}
        </button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons-round">add</span>
          {{ __('keys.create_modal.create') }}
        </button>
      </div>
    </form>
  </div>
</div>

@endsection