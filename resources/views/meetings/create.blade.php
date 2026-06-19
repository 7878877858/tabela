@extends('layouts.app')
@section('title', __('Meetings.create_meeting'))

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<style>
.cf-wrap { padding: 20px; }
.cf-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.cf-card { background:#fff; border-radius:12px; border:1px solid #e8e8e8; padding:24px; }
.cf-section-title { font-size:11px; font-weight:600; color:#999; text-transform:uppercase; letter-spacing:.06em; margin:0 0 14px; padding-bottom:8px; border-bottom:1px solid #f0f0f0; }
.cf-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.cf-grid-1 { display:grid; grid-template-columns:1fr; gap:16px; }
.cf-group { display:flex; flex-direction:column; gap:5px; }
.cf-group label { font-size:12px; font-weight:500; color:#555; }
.cf-group input,
.cf-group textarea,
.cf-group select { padding:9px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; color:#333; outline:none; width:100%; transition:border-color .15s; background:#fff; }
.cf-group input:focus,
.cf-group textarea:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,0.08); }
.cf-group textarea { resize:vertical; min-height:80px; }
.cf-divider { border:none; border-top:1px solid #f0f0f0; margin:20px 0; }
.cf-footer { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.btn-save { display:inline-flex; align-items:center; gap:6px; background:#185FA5; color:#fff; padding:9px 22px; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; }
.btn-save:hover { background:#0C447C; }
.btn-cancel { display:inline-flex; align-items:center; gap:6px; background:#fff; color:#555; padding:9px 18px; border-radius:8px; font-size:13px; border:1px solid #e0e0e0; text-decoration:none; }
.btn-cancel:hover { background:#f5f5f5; color:#333; }
.cf-required { color:#e53e3e; margin-left:2px; }

/* Select2 override */
.select2-container .select2-selection--multiple { border:1px solid #e0e0e0 !important; border-radius:8px !important; min-height:42px !important; padding:4px 8px !important; }
.select2-container--default.select2-container--focus .select2-selection--multiple { border-color:#185FA5 !important; box-shadow:0 0 0 3px rgba(24,95,165,0.08) !important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background:#EFF6FF; border:1px solid #BFDBFE; color:#1D4ED8; border-radius:5px; font-size:12px; padding:1px 8px; }

@media (max-width: 640px) {
  .cf-wrap { padding: 12px; }
  .cf-grid-2 { grid-template-columns: 1fr; }
  .cf-footer { flex-direction:column-reverse; }
  .btn-save, .btn-cancel { width:100%; justify-content:center; }
}
</style>

<div class="cf-wrap">

  {{-- Header --}}
  <!-- <div class="cf-header">
    <div>
      <h5 style="font-weight:700;margin:0 0 3px">{{ __('Meetings.create_meeting') }}</h5>
      <span style="font-size:13px;color:#888">{{ __('Meetings.meetings') }}</span>
    </div>
    <a href="{{ route('meetings.index') }}" class="btn-cancel">
      <i class="ti ti-arrow-left" style="font-size:14px"></i> {{ __('Meetings.meetings') }}
    </a>
  </div> -->

  <div class="cf-card">
    <form action="{{ route('meetings.store') }}" method="POST">
      @csrf

      {{-- Basic Info --}}
      <p class="cf-section-title"><i class="ti ti-info-circle" style="font-size:13px;vertical-align:-1px;margin-right:4px"></i>{{ __('Meetings.basic_information') }}</p>
      <div class="cf-grid-2">

        <div class="cf-group">
          <label>{{ __('Meetings.meeting_title') }} <span class="cf-required">*</span></label>
          <input type="text" name="title"
                 placeholder="{{ __('Meetings.enter_meeting_title') }}"
                 value="{{ old('title') }}" required>
          @error('title')<span style="font-size:11px;color:#e53e3e">{{ $message }}</span>@enderror
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.meeting_date') }} <span class="cf-required">*</span></label>
          <input type="date" name="meeting_date" value="{{ old('meeting_date') }}" required>
          @error('meeting_date')<span style="font-size:11px;color:#e53e3e">{{ $message }}</span>@enderror
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.start_time') }} <span class="cf-required">*</span></label>
          <input type="time" name="start_time" value="{{ old('start_time') }}" required>
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.end_time') }} <span class="cf-required">*</span></label>
          <input type="time" name="end_time" value="{{ old('end_time') }}" required>
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.location') }}</label>
          <input type="text" name="location"
                 placeholder="{{ __('Meetings.enter_location') }}"
                 value="{{ old('location') }}">
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.meeting_link') }}</label>
          <input type="url" name="meeting_link"
                 placeholder="https://meet.google.com/..."
                 value="{{ old('meeting_link') }}">
        </div>

      </div>

      <hr class="cf-divider">

      {{-- Details --}}
      <p class="cf-section-title"><i class="ti ti-file-text" style="font-size:13px;vertical-align:-1px;margin-right:4px"></i>{{ __('Meetings.details') }}</p>
      <div class="cf-grid-1">

        <div class="cf-group">
          <label>{{ __('Meetings.agenda') }}</label>
          <textarea name="agenda" rows="3"
                    placeholder="{{ __('Meetings.enter_agenda') }}">{{ old('agenda') }}</textarea>
        </div>

        <div class="cf-group">
          <label>{{ __('Meetings.description') }}</label>
          <textarea name="description" rows="3"
                    placeholder="{{ __('Meetings.enter_description') }}">{{ old('description') }}</textarea>
        </div>

      </div>

      <hr class="cf-divider">

      {{-- Participants --}}
      <p class="cf-section-title"><i class="ti ti-users" style="font-size:13px;vertical-align:-1px;margin-right:4px"></i>{{ __('Meetings.participants') }}</p>
      <div class="cf-group">
        <label>{{ __('Meetings.select_participants') }}</label>
        <select class="select2" name="participants[]" multiple>
          <optgroup label="Admins">
            @foreach($users as $user)
              <option value="user_{{ $user->id }}" {{ collect(old('participants',[])) ->contains('user_'.$user->id) ? 'selected' : '' }}>
                {{ $user->name }}
              </option>
            @endforeach
          </optgroup>
          <optgroup label="Employees">
            @foreach($employees as $employee)
              <option value="employee_{{ $employee->id }}" {{ collect(old('participants',[])) ->contains('employee_'.$employee->id) ? 'selected' : '' }}>
                {{ $employee->name }}
              </option>
            @endforeach
          </optgroup>
        </select>
      </div>

      {{-- Footer --}}
      <div class="cf-footer">
        <a href="{{ route('meetings.index') }}" class="btn-cancel">
          {{ __('Meetings.actions') }}
        </a>
        <button type="submit" class="btn-save">
          <i class="ti ti-device-floppy" style="font-size:15px"></i>
          {{ __('Meetings.save_meeting') }}
        </button>
      </div>

    </form>
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
  if (window.ErpSelect) {
    ErpSelect.initSelect2All('.select2', {
      placeholder: '{{ __("Meetings.select_participants_placeholder") }}',
    });
  } else {
    $('.select2').select2({
      placeholder: '{{ __("Meetings.select_participants_placeholder") }}',
      width: '100%',
      dropdownParent: $(document.body),
    });
  }
});
</script>

@endsection