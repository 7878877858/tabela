@props([
    'paginator',
    'perPage' => \App\Support\ListPagination::DEFAULT,
    'perPageOptions' => \App\Support\ListPagination::OPTIONS,
    'id' => 'listing',
])

<x-erp-listing :paginator="$paginator" :per-page="$perPage" :per-page-options="$perPageOptions" :id="$id">
    {{ $slot }}
</x-erp-listing>
