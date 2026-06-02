{{-- resources/views/buffalo/index.blade.php --}}
@extends('layouts.app')
@section('title', __('buffalo.all_buffaloes'))

@section('content')
<div class="page-header">
    <h2>🐃 {{ __('buffalo.all_buffaloes') }}</h2>
    <a href="{{ route('buffalo.create') }}" class="btn btn-primary">➕ {{ __('buffalo.new_buffalo') }}</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>{{ __('buffalo.tag_number') }}</th><th>{{ __('buffalo.name') }}</th><th>{{ __('buffalo.status') }}</th><th>{{ __('buffalo.milk') }}</th><th>{{ __('buffalo.this_month') }}</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($buffaloes as $b)
                <tr>
                    <td><strong>{{ $b->tag_number }}</strong></td>
                    <td>{{ $b->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $b->status==='active' ? 'badge-green' : ($b->status==='sold' ? 'badge-blue' : 'badge-red') }}">
                            {{ $b->status_label }}
                        </span>
                        <span class="badge badge-gray" style="margin-left:4px;">{{ $b->lactation_label }}</span>
                    </td>
                    <td>{{ $b->milk_entries_count }} {{ __('buffalo.days') }}.</td>
                    <td><strong>{{ number_format($b->totalMilkThisMonth(),1) }}</strong></td>
                    <td style="display:flex; gap:6px;">
                        <a href="{{ route('buffalo.show',$b) }}" class="btn btn-outline btn-sm">👁</a>
                        <a href="{{ route('buffalo.edit',$b) }}" class="btn btn-ghost btn-sm">✏️</a>
                       <form method="POST"
      action="{{ route('buffalo.destroy',$b) }}"
      onsubmit="return confirm('{{ __('buffalo.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">
                    <a href="{{ route('buffalo.create') }}">➕ {{ __('buffalo.new_buffalo') }}</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $buffaloes->links() }}</div>
</div>
@endsection