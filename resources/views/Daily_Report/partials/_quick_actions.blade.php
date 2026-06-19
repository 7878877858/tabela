@php
    $vaccDue = $vaccinationDueAnimals ?? collect();
    $pregDue = $pregnancyCheckDueAnimals ?? collect();
    $treatDue = $treatmentFollowUpAnimals ?? collect();
@endphp

@if($vaccDue->count() || $pregDue->count() || $treatDue->count())
<div class="dr-quick-actions card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <div class="dr-quick-actions__title mb-2">⚡ ઝડપી ક્રિયાઓ</div>
        <div class="dr-quick-actions__buttons">
            @if($vaccDue->count())
            <button type="button" class="btn btn-sm btn-outline-warning dr-quick-action" data-step="5" data-filter="vaccination" data-ids="{{ $vaccDue->pluck('id')->implode(',') }}">
                💉 Vaccination Due ({{ $vaccDue->count() }})
            </button>
            @endif
            @if($pregDue->count())
            <button type="button" class="btn btn-sm btn-outline-info dr-quick-action" data-step="6" data-filter="pregnancy" data-ids="{{ $pregDue->pluck('id')->implode(',') }}">
                🤰 Pregnancy Due ({{ $pregDue->count() }})
            </button>
            @endif
            @if($treatDue->count())
            <button type="button" class="btn btn-sm btn-outline-danger dr-quick-action" data-step="4" data-filter="health" data-ids="{{ $treatDue->pluck('id')->implode(',') }}">
                🏥 Treatment Follow-up ({{ $treatDue->count() }})
            </button>
            @endif
        </div>
    </div>
</div>
@endif
