{{-- resources/views/buffalo/index.blade.php --}}
@extends('layouts.app')
@section('title','ભેંસો')

@section('content')
<div class="page-header">
    <h2>🐃 બધી ભેંસો</h2>
    <a href="{{ route('buffalo.create') }}" class="btn btn-primary">➕ નવી ભેંસ</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ટેગ</th><th>નામ</th><th>સ્થિતિ</th><th>દૂધ</th><th>આ મહ. (L)</th><th></th></tr>
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
                    <td>{{ $b->milk_entries_count }} દિ.</td>
                    <td><strong>{{ number_format($b->totalMilkThisMonth(),1) }}</strong></td>
                    <td style="display:flex; gap:6px;">
                        <a href="{{ route('buffalo.show',$b) }}" class="btn btn-outline btn-sm">👁</a>
                        <a href="{{ route('buffalo.edit',$b) }}" class="btn btn-ghost btn-sm">✏️</a>
                        <form method="POST" action="{{ route('buffalo.destroy',$b) }}" onsubmit="return confirm('ખરેખર ડિલીટ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">
                    <a href="{{ route('buffalo.create') }}">➕ પ્રથમ ભેંસ ઉમેરો</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $buffaloes->links() }}</div>
</div>
@endsection