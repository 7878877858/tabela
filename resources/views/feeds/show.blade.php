@extends('layouts.app')

@section('title', 'Feed Details - '.$feed->name)

@section('content')

<div class="page-header">
    <h2>🌾 {{ $feed->name }}</h2>

    <div style="display:flex; gap:8px;">
        <a href="{{ route('feeds.edit',$feed) }}"
           class="btn btn-outline btn-sm">
            ✏️ Edit
        </a>

        <a href="{{ route('feeds.index') }}"
           class="btn btn-ghost btn-sm">
            ← Back
        </a>
    </div>
</div>

<div class="grid-2" style="margin-bottom:20px;">

    <div class="card">

        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;color:#6b7280;">
            📋 Feed Details
        </h3>

        <table style="font-size:14px; width:100%;">

            <tr>
                <td style="color:#6b7280;padding:8px 0;width:180px;">
                    Feed Name
                </td>

                <td>
                    <strong>{{ $feed->name }}</strong>
                </td>
            </tr>

            <tr>
                <td style="color:#6b7280;padding:8px 0;">
                    Available Stock
                </td>

                <td>
                    <span class="badge badge-blue">
                        {{ $feed->volume }} {{ $feed->unit }}
                    </span>
                </td>
            </tr>

            <tr>
                <td style="color:#6b7280;padding:8px 0;">
                    Unit
                </td>

                <td>
                    {{ $feed->unit }}
                </td>
            </tr>

            <tr>
                <td style="color:#6b7280;padding:8px 0;">
                    Status
                </td>

                <td>
                    <span class="badge {{ $feed->status ? 'badge-green' : 'badge-red' }}">
                        {{ $feed->status ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>

            <tr>
                <td style="color:#6b7280;padding:8px 0;">
                    Created
                </td>

                <td>
                    {{ $feed->created_at->format('d/m/Y') }}
                </td>
            </tr>

        </table>

    </div>

    <div class="card">

        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;color:#6b7280;">
            📝 Description
        </h3>

        <div style="line-height:1.8;">
            {{ $feed->description ?: 'No description available.' }}
        </div>

    </div>

</div>

@endsection