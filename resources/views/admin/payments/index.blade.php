@extends('admin.layout')

@section('title', "To'lovlar")
@section('page_title', "To'lovlar")

@push('styles')
<style>
.pay-page { padding: 24px; max-width: 1400px; margin: 0 auto; }

.tab-bar {
  display: flex;
  gap: 4px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
  margin-bottom: 16px;
  width: fit-content;
}

.tab-btn {
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: 7px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.tab-btn:hover { color: var(--text-strong); }
.tab-btn.active { background: var(--text-strong); color: var(--bg-elevated); }

.tab-count {
  font-size: 10px;
  padding: 1px 6px;
  background: var(--bg-subtle);
  border-radius: 99px;
  color: var(--text-muted);
}

.tab-btn.active .tab-count { background: rgba(255,255,255,.18); color: white; }

.search-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.search-input {
  flex: 1;
  max-width: 400px;
  padding: 9px 14px 9px 40px;
  font-size: 13px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 9px;
  outline: none;
  position: relative;
}

.search-wrap { position: relative; flex: 1; max-width: 400px; }
.search-wrap .material-icons-round {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: var(--text-subtle);
}

.pay-table-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

table { width: 100%; border-collapse: collapse; font-size: 13px; }

th {
  text-align: left;
  font-size: 10px; font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase; letter-spacing: 0.08em;
  padding: 12px 16px;
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border);
}

td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }

.user-cell { display: flex; align-items: center; gap: 10px; }

.user-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
  display: block;
}

.user-email {
  font-size: 11px;
  color: var(--text-muted);
}

.amount {
  font-size: 14px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-strong);
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  font-size: 10px;
  font-weight: 700;
  border-radius: 99px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.status-badge .material-icons-round { font-size: 11px; }
.status-pending { background: rgba(245,158,11,.12); color: var(--warning); }
.status-completed { background: rgba(16,185,129,.12); color: var(--success); }
.status-failed { background: rgba(239,68,68,.12); color: var(--danger); }

.actions { display: flex; gap: 4px; justify-content: flex-end; }

.action-btn {
  width: 32px; height: 32px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-muted);
  display: flex; align-items: center; justify-content: center;
}

.action-btn:hover { background: var(--bg-subtle); color: var(--text-strong); border-color: var(--border-strong); }
.action-btn .material-icons-round { font-size: 16px; }

.empty {
  text-align: center;
  padding: 80px 20px;
  color: var(--text-muted);
}

.empty .material-icons-round {
  font-size: 48px; color: var(--text-subtle);
  margin-bottom: 16px; opacity: 0.6;
}

.empty h3 { font-size: 16px; color: var(--text-strong); margin-bottom: 6px; }
.empty p { font-size: 13px; }

.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); }

.tx-id { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); }
.tx-date { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="pay-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">To'lovlar</h1>
      <p class="page-subtitle">Manual to'lovlarni tasdiqlash va boshqarish</p>
    </div>
  </div>

  <!-- Status tabs -->
  <div class="tab-bar">
    <a href="?status=pending" class="tab-btn {{ $status === 'pending' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">hourglass_top</span>
      Kutilmoqda
      <span class="tab-count">{{ $counts['pending'] }}</span>
    </a>
    <a href="?status=completed" class="tab-btn {{ $status === 'completed' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">check_circle</span>
      Tasdiqlangan
      <span class="tab-count">{{ $counts['completed'] }}</span>
    </a>
    <a href="?status=failed" class="tab-btn {{ $status === 'failed' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">cancel</span>
      Rad etilgan
      <span class="tab-count">{{ $counts['failed'] }}</span>
    </a>
    <a href="?status=all" class="tab-btn {{ $status === 'all' ? 'active' : '' }}">
      Hammasi
      <span class="tab-count">{{ $counts['all'] }}</span>
    </a>
  </div>

  <!-- Search -->
  <form class="search-bar" method="GET">
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="search-wrap">
      <span class="material-icons-round">search</span>
      <input type="text" name="q" class="search-input" placeholder="User nomi yoki email..." value="{{ request('q') }}">
    </div>
  </form>

  <div class="pay-table-card">
    @if($payments->isEmpty())
      <div class="empty">
        <span class="material-icons-round">payments</span>
        <h3>To'lovlar yo'q</h3>
        <p>Bu kategoriyada to'lovlar topilmadi</p>
      </div>
    @else
      <table>
        <thead>
          <tr>
            <th>Foydalanuvchi</th>
            <th>Miqdor</th>
            <th>Holat</th>
            <th>Sana</th>
            <th>TX ID</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $tx)
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ strtoupper(substr($tx->user->name ?? '?', 0, 1)) }}</div>
                <div>
                  <span class="user-name">{{ $tx->user->name ?? 'Unknown' }}</span>
                  <span class="user-email">{{ $tx->user->email ?? '—' }}</span>
                </div>
              </div>
            </td>
            <td><span class="amount">+{{ number_format($tx->amount_uzs, 0, '.', ' ') }} so'm</span></td>
            <td>
              @if($tx->status === 'pending')
                <span class="status-badge status-pending">
                  <span class="material-icons-round">hourglass_top</span>
                  Kutmoqda
                </span>
              @elseif($tx->status === 'completed')
                <span class="status-badge status-completed">
                  <span class="material-icons-round">check_circle</span>
                  Tasdiqlangan
                </span>
              @else
                <span class="status-badge status-failed">
                  <span class="material-icons-round">cancel</span>
                  Rad etilgan
                </span>
              @endif
            </td>
            <td><span class="tx-date">{{ $tx->created_at->format('M d, H:i') }}</span></td>
            <td><span class="tx-id">#{{ $tx->id }}</span></td>
            <td>
              <div class="actions">
                <a href="{{ route('admin.payments.show', $tx) }}" class="action-btn" title="Ko'rish">
                  <span class="material-icons-round">visibility</span>
                </a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @if($payments->hasPages())
        <div class="pagination-wrap">{{ $payments->links() }}</div>
      @endif
    @endif
  </div>
</div>
@endsection