@extends('layouts.app')
@section('title', $meeting->title)

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

<style>
.sv-wrap { padding: 20px; }
.sv-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
.sv-title { font-size:20px; font-weight:700; color:#1a1a1a; margin:0 0 4px; }
.sv-sub { font-size:12px; color:#999; }
.sv-actions { display:flex; gap:8px; }
.btn-back { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; border:1px solid #e0e0e0; background:#fff; color:#555; font-size:13px; text-decoration:none; }
.btn-back:hover { background:#f5f5f5; color:#333; }
.btn-edit { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; background:#FFFBEB; border:1px solid #FDE68A; color:#92400E; font-size:13px; font-weight:500; text-decoration:none; }
.btn-edit:hover { background:#FEF3C7; color:#78350F; }

/* Status badge */
.sv-status { margin-bottom:20px; }
.sv-badge { font-size:12px; padding:4px 12px; border-radius:20px; font-weight:500; display:inline-block; }
.badge-scheduled { background:#ecfdf5; color:#065f46; }
.badge-ongoing   { background:#dbeafe; color:#1e40af; }
.badge-completed { background:#f0fdf4; color:#166534; }
.badge-cancelled { background:#fef2f2; color:#991b1b; }

/* Info cards row */
.sv-info-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.sv-info-card { background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:16px; display:flex; flex-direction:column; align-items:center; text-align:center; gap:8px; }
.sv-info-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.sv-info-label { font-size:11px; color:#999; text-transform:uppercase; letter-spacing:.05em; }
.sv-info-value { font-size:14px; font-weight:600; color:#1a1a1a; }

/* Detail card */
.sv-card { background:#fff; border:1px solid #e8e8e8; border-radius:12px; margin-bottom:14px; }
.sv-card-header { padding:12px 18px; border-bottom:1px solid #f0f0f0; font-size:11px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.06em; display:flex; align-items:center; gap:6px; }
.sv-card-body { padding:16px 18px; font-size:13px; color:#444; line-height:1.6; }
.sv-card-empty { color:#bbb; font-style:italic; }

/* Participants */
.sv-participant { display:inline-flex; align-items:center; gap:6px; background:#EFF6FF; border:1px solid #BFDBFE; color:#1D4ED8; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:500; margin:3px; }
.sv-participant .avatar { width:20px; height:20px; border-radius:50%; background:#BFDBFE; color:#1D4ED8; font-size:9px; font-weight:600; display:flex; align-items:center; justify-content:center; }

/* 2-col layout */
.sv-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

@media (max-width:768px) {
  .sv-wrap { padding:12px; }
  .sv-info-grid { grid-template-columns:repeat(2,1fr); }
  .sv-row { grid-template-columns:1fr; }
  .sv-header { flex-direction:column; gap:10px; }
  .sv-actions { width:100%; }
  .btn-back, .btn-edit { flex:1; justify-content:center; }
}
</style>

<div class="sv-wrap">

  {{-- Header --}}
  <div class="sv-header">
    <div>
      <h4 class="sv-title">{{ $meeting->title }}</h4>
      <span class="sv-sub">{{ __('Meetings.meetings') }} #{{ $meeting->id }}</span>
    </div>
    <div class="sv-actions">
      <a href="{{ route('meetings.index') }}" class="btn-back">
        <i class="ti ti-arrow-left" style="font-size:14px"></i> {{ __('Meetings.meetings') }}
      </a>
      <a href="{{ route('meetings.edit', $meeting->id) }}" class="btn-edit">
        <i class="ti ti-edit" style="font-size:14px"></i> {{ __('Meetings.edit_meeting') }}
      </a>
    </div>
  </div>

  {{-- Status --}}
  <div class="sv-status">
    @php
      $st = $meeting->status ?? 'scheduled';
      $stClass = match($st) {
        'ongoing'   => 'badge-ongoing',
        'completed' => 'badge-completed',
        'cancelled' => 'badge-cancelled',
        default     => 'badge-scheduled',
      };
      $stLabel = match($st) {
        'ongoing'   => __('Meetings.ongoing'),
        'completed' => __('Meetings.completed'),
        'cancelled' => __('Meetings.cancelled'),
        default     => __('Meetings.scheduled'),
      };
    @endphp
    <span class="sv-badge {{ $stClass }}">{{ $stLabel }}</span>
  </div>

  {{-- Info Cards --}}
  <div class="sv-info-grid">
    <div class="sv-info-card">
      <div class="sv-info-icon" style="background:#EFF6FF">
        <i class="ti ti-calendar" style="color:#185FA5"></i>
      </div>
      <div class="sv-info-label">{{ __('Meetings.meeting_date') }}</div>
      <div class="sv-info-value">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}</div>
    </div>

    <div class="sv-info-card">
      <div class="sv-info-icon" style="background:#FFFBEB">
        <i class="ti ti-clock" style="color:#BA7517"></i>
      </div>
      <div class="sv-info-label">{{ __('Meetings.start_time') }} – {{ __('Meetings.end_time') }}</div>
      <div class="sv-info-value">{{ $meeting->start_time }} – {{ $meeting->end_time }}</div>
    </div>

    <div class="sv-info-card">
      <div class="sv-info-icon" style="background:#FEF2F2">
        <i class="ti ti-map-pin" style="color:#c0392b"></i>
      </div>
      <div class="sv-info-label">{{ __('Meetings.location') }}</div>
      <div class="sv-info-value">{{ $meeting->location ?: '—' }}</div>
    </div>

    <div class="sv-info-card">
      <div class="sv-info-icon" style="background:#F0FDF4">
        <i class="ti ti-video" style="color:#1D9E75"></i>
      </div>
      <div class="sv-info-label">{{ __('Meetings.meeting_link') }}</div>
      @if($meeting->meeting_link)
        <a href="{{ $meeting->meeting_link }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:500;background:#185FA5;color:#fff;padding:5px 12px;border-radius:6px;text-decoration:none">
          <i class="ti ti-external-link" style="font-size:12px"></i> Join
        </a>
      @else
        <div class="sv-info-value" style="color:#bbb;font-weight:400;font-style:italic">Not set</div>
      @endif
    </div>
  </div>

  {{-- Agenda + Description --}}
  <div class="sv-row">
    <div class="sv-card">
      <div class="sv-card-header">
        <i class="ti ti-list" style="font-size:13px;color:#185FA5"></i>
        {{ __('Meetings.agenda') }}
      </div>
      <div class="sv-card-body">
        @if($meeting->agenda)
          {{ $meeting->agenda }}
        @else
          <span class="sv-card-empty">No agenda available</span>
        @endif
      </div>
    </div>

    <div class="sv-card">
      <div class="sv-card-header">
        <i class="ti ti-file-text" style="font-size:13px;color:#1D9E75"></i>
        {{ __('Meetings.description') }}
      </div>
      <div class="sv-card-body">
        @if($meeting->description)
          {{ $meeting->description }}
        @else
          <span class="sv-card-empty">No description available</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Participants --}}
  <div class="sv-card">
    <div class="sv-card-header">
      <i class="ti ti-users" style="font-size:13px;color:#185FA5"></i>
      {{ __('Meetings.participants') }}
    </div>
    <div class="sv-card-body">
      @forelse($meeting->participants as $p)
        <span class="sv-participant">
          <span class="avatar">{{ strtoupper(substr($p->name,0,1)) }}</span>
          {{ $p->name }}
        </span>
      @empty
        <span class="sv-card-empty">{{ __('Meetings.no_meetings_found') }}</span>
      @endforelse
    </div>
  </div>

</div>

@endsection