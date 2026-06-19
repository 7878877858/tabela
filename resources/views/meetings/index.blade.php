@extends('layouts.app')
@section('title', __('Meetings.meetings'))

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

<style>
.mt-wrap { padding: 20px; }
.mt-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
.mt-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.mt-toolbar { background:#fff; border-radius:10px; border:1px solid #e8e8e8; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.mt-tabs { display:flex; gap:3px; background:#f3f3f3; border-radius:8px; padding:3px; flex-wrap:wrap; }
.mt-tab { background:transparent; color:#555; border:none; padding:5px 13px; border-radius:6px; font-size:12px; cursor:pointer; white-space:nowrap; }
.mt-tab.active { background:#185FA5; color:#fff; font-weight:500; }
.mt-search-wrap { margin-left:auto; display:flex; gap:8px; align-items:center; }
.mt-search-wrap input { padding:7px 12px 7px 30px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; width:200px; outline:none; color:#333; }
.mt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; }
.mt-card { background:#fff; border-radius:12px; border:1px solid #e8e8e8; padding:16px; display:flex; flex-direction:column; transition:box-shadow .15s; }
.mt-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.mt-meta-box { display:flex; flex-direction:column; gap:6px; margin:10px 0; padding:10px 12px; background:#fafafa; border-radius:8px; }
.mt-actions { display:flex; gap:8px; margin-top:auto; padding-top:4px; }
.mt-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:5px; padding:7px 6px; border-radius:7px; font-size:12px; font-weight:500; text-decoration:none; cursor:pointer; border-width:1px; border-style:solid; white-space:nowrap; }
.mt-btn-view   { background:#EFF6FF; border-color:#BFDBFE; color:#1D4ED8; }
.mt-btn-edit   { background:#FFFBEB; border-color:#FDE68A; color:#92400E; }
.mt-btn-delete { background:#FEF2F2; border-color:#FECACA; color:#991B1B; width:100%; }

@media (max-width: 768px) {
  .mt-wrap { padding: 12px; }
  .mt-stats { grid-template-columns: repeat(2,1fr); }
  .mt-grid { grid-template-columns: 1fr; }
  .mt-search-wrap { margin-left:0; width:100%; }
  .mt-search-wrap input { width:100%; }
  .mt-toolbar { gap:8px; }
  .mt-tabs { width:100%; }
  .mt-tab { flex:1; text-align:center; padding:6px 8px; }
  .mt-actions.mobile-card-actions {
    justify-content: flex-end;
    flex-wrap: nowrap;
    gap: 8px;
    padding-top: 8px;
    border-top: 1px solid #e8e8e8;
    margin-top: 8px;
  }
  .mt-actions .mt-btn {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    min-width: 38px;
    min-height: 38px;
    padding: 0;
    font-size: 0;
    overflow: hidden;
  }
  .mt-actions .mt-btn i {
    font-size: 16px;
    margin: 0;
  }
  .mt-actions form {
    flex: 0 0 auto;
    display: inline-flex;
    margin: 0;
  }
}
@media (max-width: 480px) {
  .mt-header { flex-direction:column; gap:10px; }
  .mt-header a { width:100%; justify-content:center; }
}
</style>

<div class="mt-wrap">

  {{-- Header --}}
  <div class="mt-header">
    <div>
      <h5 style="font-weight:700;margin:0 0 3px">{{ __('Meetings.meetings') }}</h5>
      <span style="font-size:13px;color:#888">{{ __('Meetings.manage_meetings') }}</span>
    </div>
    <a href="{{ route('meetings.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#185FA5;color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">
      <i class="ti ti-plus" style="font-size:15px"></i> {{ __('Meetings.create_meeting') }}
    </a>
  </div>

  {{-- Stats --}}
  @php
    $cntToday = $meetingStats['today'] ?? 0;
    $cntUpcoming = $meetingStats['upcoming'] ?? 0;
    $cntDone = $meetingStats['completed'] ?? 0;
  @endphp
  <div class="mt-stats">
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e8e8e8">
      <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ __('Meetings.total') }}</div>
      <div style="font-size:26px;font-weight:700;color:#185FA5">{{ $meetingStats['total'] ?? 0 }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e8e8e8">
      <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ __('Meetings.today') }}</div>
      <div style="font-size:26px;font-weight:700;color:#1D9E75">{{ $meetingStats['today'] ?? 0 }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e8e8e8">
      <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ __('Meetings.upcoming') }}</div>
      <div style="font-size:26px;font-weight:700;color:#BA7517">{{ $meetingStats['upcoming'] ?? 0 }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e8e8e8">
      <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ __('Meetings.completed') }}</div>
      <div style="font-size:26px;font-weight:700;color:#888">{{ $meetingStats['completed'] ?? 0 }}</div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="mt-toolbar">
    <div class="mt-tabs">
      <button class="mt-tab active" onclick="mtFilter('all',this)">{{ __('Meetings.all') }}</button>
      <button class="mt-tab" onclick="mtFilter('today',this)">{{ __('Meetings.today') }}</button>
      <button class="mt-tab" onclick="mtFilter('upcoming',this)">{{ __('Meetings.upcoming') }}</button>
      <button class="mt-tab" onclick="mtFilter('completed',this)">{{ __('Meetings.completed') }}</button>
    </div>
    <div class="mt-search-wrap">
      <div style="position:relative;flex:1">
        <i class="ti ti-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;pointer-events:none"></i>
        <input type="text" oninput="mtSearch(this.value)" placeholder="{{ __('Meetings.search_meetings') }}" style="width:100%">
      </div>
      <button style="display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;color:#555;font-size:12px;cursor:pointer;white-space:nowrap">
        <i class="ti ti-adjustments-horizontal" style="font-size:14px"></i> {{ __('Meetings.filters') }}
      </button>
    </div>
  </div>

  {{-- Cards --}}
  @if($meetings->total())
  <x-erp-listing :paginator="$meetings" :per-page="$perPage" :search="true" search-placeholder="મીટિંગ શોધો..." id="meetings">
  <div class="mt-grid" id="mtGrid">
    @foreach($meetings as $meeting)
    @php
      $st = $meeting->status ?? 'scheduled';
      $bStyle = match($st) {
        'ongoing'   => 'background:#dbeafe;color:#1e40af',
        'completed' => 'background:#f0fdf4;color:#166534',
        'cancelled' => 'background:#fef2f2;color:#991b1b',
        default     => 'background:#ecfdf5;color:#065f46',
      };
      $isToday  = \Carbon\Carbon::parse($meeting->meeting_date)->isToday();
      $isFuture = \Carbon\Carbon::parse($meeting->meeting_date)->isFuture() && !$isToday;
      $fAttr    = $isToday ? 'today' : ($isFuture ? 'upcoming' : ($st==='completed' ? 'completed' : 'past'));
    @endphp
    <div class="mt-card" data-filter="{{ $fAttr }}" data-title="{{ strtolower($meeting->title) }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div>
          <div style="font-size:15px;font-weight:600;color:#1a1a1a;margin-bottom:5px">{{ $meeting->title }}</div>
          <span style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:500;{{ $bStyle }}">{{ ucfirst($st) }}</span>
        </div>
        <span style="font-size:11px;color:#ccc">{{ __('common.sr_no') }} {{ $meetings->firstItem() + $loop->index }}</span>
      </div>

      <div class="mt-meta-box">
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#555">
          <i class="ti ti-calendar" style="color:#185FA5;font-size:14px;flex-shrink:0"></i>
          {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#555">
          <i class="ti ti-clock" style="color:#BA7517;font-size:14px;flex-shrink:0"></i>
          {{ $meeting->start_time }} – {{ $meeting->end_time }}
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#555">
          <i class="ti ti-map-pin" style="color:#c0392b;font-size:14px;flex-shrink:0"></i>
          {{ $meeting->location ?: 'No location set' }}
        </div>
      </div>

      <div class="mt-actions mobile-card-actions">
        <a href="{{ route('meetings.show',$meeting->id) }}" class="mt-btn mt-btn-view">
          <i class="ti ti-eye" style="font-size:14px"></i> {{ __('Meetings.view') }}
        </a>
        <a href="{{ route('meetings.edit',$meeting->id) }}" class="mt-btn mt-btn-edit">
          <i class="ti ti-edit" style="font-size:14px"></i> {{ __('Meetings.edit') }}
        </a>
        <form action="{{ route('meetings.destroy',$meeting->id) }}" method="POST" style="flex:1" onsubmit="return confirm('Delete this meeting?')">
          @csrf @method('DELETE')
          <button type="submit" class="mt-btn mt-btn-delete">
            <i class="ti ti-trash" style="font-size:14px"></i> {{ __('Meetings.delete') }}
          </button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
  </x-erp-listing>

  @else
  <div style="background:#fff;border-radius:12px;border:1px solid #e8e8e8;padding:60px 20px;text-align:center">
    <i class="ti ti-calendar-off" style="font-size:48px;color:#ddd;display:block;margin-bottom:16px"></i>
    <h5 style="font-weight:600;margin-bottom:6px">{{ __('Meetings.no_meetings_found') }}</h5>
    <p style="color:#999;font-size:13px;margin-bottom:20px">{{ __('Meetings.create_first_meeting') }}</p>
    <a href="{{ route('meetings.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#185FA5;color:#fff;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">
      <i class="ti ti-plus"></i> {{ __('Meetings.create_first_meeting') }}
    </a>
  </div>
  @endif

</div>

<script>
function mtFilter(type, btn) {
  document.querySelectorAll('.mt-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.mt-card').forEach(el => {
    const f = el.dataset.filter;
    el.style.display = (type==='all' || f===type) ? '' : 'none';
  });
}
function mtSearch(q) {
  const s = q.toLowerCase();
  document.querySelectorAll('.mt-card').forEach(el => {
    el.style.display = el.dataset.title.includes(s) ? '' : 'none';
  });
}
</script>

@endsection