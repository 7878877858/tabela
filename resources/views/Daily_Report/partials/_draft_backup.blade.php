{{-- Offline draft UI (IndexedDB) — logic in daily-report-draft.js --}}
<style>
    #dailyReportDraftBadge {
        position: fixed;
        top: 72px;
        right: 16px;
        z-index: 1050;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        background: #fff;
        border: 1px solid #e5e7eb;
        display: none;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        min-width: 140px;
    }

    #dailyReportDraftBadge.visible {
        display: flex;
    }

    #dailyReportDraftBadge.saving {
        color: #b45309;
        border-color: #fcd34d;
        background: #fffbeb;
    }

    #dailyReportDraftBadge.saved {
        color: #15803d;
        border-color: #86efac;
        background: #f0fdf4;
    }

    #dailyReportDraftBadge .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
        margin-right: 4px;
    }

    #dailyReportDraftBadgeText {
        display: inline-flex;
        align-items: center;
    }

    #dailyReportDraftBadgeTime {
        font-size: 11px;
        font-weight: 500;
        opacity: 0.85;
    }

    #dailyReportDraftModal.dr-draft-open {
        display: block;
    }

    #dailyReportDraftModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2000;
    }

    #dailyReportDraftModal .dr-draft-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
    }

    #dailyReportDraftModal .dr-draft-dialog {
        position: relative;
        z-index: 1;
        max-width: 480px;
        margin: 10vh auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    #dailyReportDraftModal .dr-draft-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 18px;
        font-weight: 600;
    }

    #dailyReportDraftModal .dr-draft-body {
        padding: 16px 20px;
    }

    #dailyReportDraftModal .dr-draft-footer {
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<div id="dailyReportDraftBadge" role="status" aria-live="polite">
    <span id="dailyReportDraftBadgeText"><span class="dot"></span>🟢 Draft Saved</span>
    <span id="dailyReportDraftBadgeTime"></span>
</div>

<div id="dailyReportDraftModal" tabindex="-1" aria-labelledby="dailyReportDraftModalLabel" aria-hidden="true" role="dialog">
    <div class="dr-draft-backdrop"></div>
    <div class="dr-draft-dialog">
        <div class="dr-draft-header" id="dailyReportDraftModalLabel">Draft Found</div>
        <div class="dr-draft-body">
            <p class="mb-2">You have unsaved Daily Report data.</p>
            <p class="text-muted mb-0" style="font-size:13px; color:#6b7280;" id="dailyReportDraftModalMeta"></p>
        </div>
        <div class="dr-draft-footer">
            <button type="button" class="btn btn-outline-secondary" id="dailyReportDraftDiscard">Discard Draft</button>
            <button type="button" class="btn btn-primary" id="dailyReportDraftRestore">Restore Draft</button>
        </div>
    </div>
</div>

@php
    $drDraftVer = @filemtime(public_path('static/js/daily-report-draft.js')) ?: '1';
@endphp
<script src="{{ asset('static/js/daily-report-draft.js') }}?v={{ $drDraftVer }}"></script>
