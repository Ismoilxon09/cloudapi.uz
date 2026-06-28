@extends('admin.layout')

@section('title', $user->name)
@section('page_title', $user->name)

@push('styles')
<style>
.user-show { padding: 24px; max-width: 1400px; margin: 0 auto; }

.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }
.back-link:hover { color: var(--text-strong); }
.back-link .material-icons-round { font-size: 16px; }

.user-header-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.big-avatar {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; font-weight: 700;
}

.user-h-info { flex: 1; min-width: 200px; }
.user-h-name { font-size: 22px; font-weight: 800; color: var(--text-strong); margin-bottom: 4px; letter-spacing: -0.02em; }
.user-h-email { font-size: 13px; color: var(--text-muted); margin-bottom: 8px; }
.user-h-meta { display: flex; gap: 14px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap; }
.user-h-meta strong { color: var(--text-strong); font-weight: 600; }

.user-h-actions { display: flex; gap: 8px; }

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.stat-tile {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
}

.stat-label { font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
.stat-value { font-size: 20px; font-weight: 800; color: var(--text-strong); letter-spacing: -0.02em; }
.stat-value .meta { font-size: 12px; font-weight: 500; color: var(--text-muted); }

.layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 16px;
}

@media (max-width: 1000px) { .layout { grid-template-columns: 1fr; } }

.card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
}

.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.card-title { font-size: 14px; font-weight: 700; color: var(--text-strong); }

.tx-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px; border-radius: 8px;
}

.tx-row:hover { background: var(--bg-subtle); }

.tx-icon {
  width: 30px; height: 30px;
  border-radius: 7px;
  background: var(--bg-subtle);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.tx-icon .material-icons-round { font-size: 14px; }
.tx-icon.deposit { background: rgba(16,185,129,.12); color: var(--success); }
.tx-icon.usage { background: var(--bg-subtle); color: var(--text-muted); }

.tx-info { flex: 1; min-width: 0; }
.tx-desc { font-size: 12px; font-weight: 600; color: var(--text-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-date { font-size: 10px; color: var(--text-subtle); }
.tx-amount { font-size: 13px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
.tx-amount.positive { color: var(--success); }
.tx-amount.negative { color: var(--text-muted); }

.key-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border-radius: 8px;
  margin-bottom: 6px;
}

.key-prefix { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-strong); }
.key-name { font-size: 12px; font-weight: 600; color: var(--text-strong); margin-bottom: 2px; }
.key-meta { font-size: 10px; color: var(--text-muted); }

/* Adjust balance form */
.adj-form { margin-top: 14px; }
.adj-type-tabs {
  display: flex; gap: 4px;
  background: var(--bg-subtle);
  border-radius: 8px;
  padding: 3px;
  margin-bottom: 12px;
}

.adj-type-tab {
  flex: 1;
  padding: 6px;
  font-size: 11px;
  font-weight: 600;
  text-align: center;
  border-radius: 6px;
  cursor: pointer;
  color: var(--text-muted);
}

.adj-type-tab.active { background: var(--bg-elevated); color: var(--text-strong); box-shadow: var(--shadow-sm); }

.field { margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="user-show">
  <a href="{{ route('admin.users.index') }}" class="back-link">
    <span class="material-icons-round">arrow_back</span>
    Foydalanuvchilar
  </a>

  <!-- User header -->
  <div class="user-header-card">
    <div class="big-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
    <div class="user-h-info">
      <div class="user-h-name">{{ $user->name }}</div>
      <div class="user-h-email">{{ $user->email }}</div>
      <div class="user-h-meta">
        <span>ID: <strong>#{{ $user->id }}</strong></span>
        @if($user->phone)<span>Tel: <strong>{{ $user->phone }}</strong></span>@endif
        @if($user->country)<span>Mamlakat: <strong>{{ $user->country }}</strong></span>@endif
        <span>Til: <strong>{{ strtoupper($user->language ?? 'EN') }}</strong></span>
        <span>Ro'yxatdan: <strong>{{ $user->created_at->format('M d, Y') }}</strong></span>
      </div>
    </div>
    <div class="user-h-actions">
      @if($user->status === 'blocked')
        <form action="{{ route('admin.users.unblock', $user) }}" method="POST" onsubmit="return confirm('Blokdan chiqarish?')" style="margin:0">
          @csrf
          <button class="btn btn-success">
            <span class="material-icons-round">check_circle</span>
            Blokdan chiqarish
          </button>
        </form>
      @else
        <form action="{{ route('admin.users.block', $user) }}" method="POST" onsubmit="return confirm('Foydalanuvchini bloklash? Barcha API kalitlari ham bekor qilinadi.')" style="margin:0">
          @csrf
          <button class="btn btn-danger">
            <span class="material-icons-round">block</span>
            Bloklash
          </button>
        </form>
      @endif
    </div>
  </div>

  <!-- Stats -->
  <div class="stat-grid">
    <div class="stat-tile">
      <div class="stat-label">Joriy balans</div>
      <div class="stat-value">{{ number_format($user->wallet?->balance_uzs ?? 0, 0, '.', ' ') }}<span class="meta"> so'm</span></div>
    </div>
    <div class="stat-tile">
      <div class="stat-label">Jami to'ldirilgan</div>
      <div class="stat-value">{{ number_format($stats['deposits_total'], 0, '.', ' ') }}<span class="meta"> so'm</span></div>
    </div>
    <div class="stat-tile">
      <div class="stat-label">Sarflagan</div>
      <div class="stat-value">{{ number_format($stats['total_spent'], 0, '.', ' ') }}<span class="meta"> so'm</span></div>
    </div>
    <div class="stat-tile">
      <div class="stat-label">So'rovlar</div>
      <div class="stat-value">{{ number_format($stats['total_requests']) }}</div>
    </div>
    <div class="stat-tile">
      <div class="stat-label">Tokenlar</div>
      <div class="stat-value">
        @if($stats['total_tokens'] >= 1000000)
          {{ number_format($stats['total_tokens'] / 1000000, 2) }}<span class="meta">M</span>
        @elseif($stats['total_tokens'] >= 1000)
          {{ number_format($stats['total_tokens'] / 1000, 1) }}<span class="meta">K</span>
        @else
          {{ $stats['total_tokens'] }}
        @endif
      </div>
    </div>
    <div class="stat-tile">
      <div class="stat-label">Bugun so'rov</div>
      <div class="stat-value">{{ $stats['requests_today'] }}</div>
    </div>
  </div>

  <div class="layout">
    <!-- Left: Transactions + Keys -->
    <div>
      <!-- Transactions -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Oxirgi tranzaksiyalar</div>
        </div>

        @if($recentTransactions->isEmpty())
          <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:13px">Tranzaksiyalar yo'q</div>
        @else
          @foreach($recentTransactions as $tx)
          <div class="tx-row">
            <div class="tx-icon {{ $tx->type }}">
              <span class="material-icons-round">
                @if($tx->type === 'deposit') arrow_downward
                @elseif($tx->type === 'usage') data_usage
                @elseif($tx->type === 'bonus') card_giftcard
                @else circle
                @endif
              </span>
            </div>
            <div class="tx-info">
              <div class="tx-desc">{{ $tx->description ?? ucfirst($tx->type) }}</div>
              <div class="tx-date">{{ $tx->created_at->format('M d, H:i') }} · {{ ucfirst($tx->status) }}</div>
            </div>
            <div class="tx-amount {{ $tx->amount_uzs > 0 ? 'positive' : 'negative' }}">
              {{ $tx->amount_uzs > 0 ? '+' : '' }}{{ number_format($tx->amount_uzs, 0, '.', ' ') }}
            </div>
          </div>
          @endforeach
        @endif
      </div>

      <!-- API Keys -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">API kalitlar ({{ $keys->count() }})</div>
        </div>

        @if($keys->isEmpty())
          <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:13px">Kalitlar yo'q</div>
        @else
          @foreach($keys as $key)
          <div class="key-row">
            <span class="material-icons-round" style="color:var(--text-muted);font-size:18px">key</span>
            <div style="flex:1;min-width:0">
              <div class="key-name">{{ $key->name }}</div>
              <div class="key-meta">{{ $key->key_prefix }} · {{ number_format($key->total_requests) }} so'rov</div>
            </div>
            @if($key->status === 'active')
              <span class="status-badge" style="background:rgba(16,185,129,.12);color:var(--success);padding:2px 7px;font-size:9px;border-radius:99px">Faol</span>
            @else
              <span class="status-badge" style="background:var(--bg-subtle);color:var(--text-muted);padding:2px 7px;font-size:9px;border-radius:99px">{{ ucfirst($key->status) }}</span>
            @endif
          </div>
          @endforeach
        @endif
      </div>
    </div>

    <!-- Right: Adjust balance -->
    <div>
      <div class="card" style="position:sticky;top:80px">
        <div class="card-header">
          <div class="card-title">Balansni o'zgartirish</div>
        </div>

        <form action="{{ route('admin.users.balance', $user) }}" method="POST" class="adj-form">
          @csrf

          <div class="adj-type-tabs">
            <label class="adj-type-tab active" onclick="selectType(this, 'credit')">
              <input type="radio" name="type" value="credit" checked style="display:none">
              + Qo'shish
            </label>
            <label class="adj-type-tab" onclick="selectType(this, 'debit')">
              <input type="radio" name="type" value="debit" style="display:none">
              − Yechish
            </label>
            <label class="adj-type-tab" onclick="selectType(this, 'bonus')">
              <input type="radio" name="type" value="bonus" style="display:none">
              🎁 Bonus
            </label>
          </div>

          <div class="field">
            <label class="label">Miqdor (so'm)</label>
            <input type="number" name="amount" class="input" placeholder="10000" min="100" required>
          </div>

          <div class="field">
            <label class="label">Sabab</label>
            <textarea name="reason" class="input" rows="3" placeholder="Masalan: Test bonus, kompensatsiya..." required></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-full">
            <span class="material-icons-round">save</span>
            Tasdiqlash
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function selectType(label, type) {
  document.querySelectorAll('.adj-type-tab').forEach(t => t.classList.remove('active'));
  label.classList.add('active');
  label.querySelector('input').checked = true;
}
</script>
@endsection