@props([
    'births',
    'tableClass' => 'report-table',
    'emptyMessage' => 'ડેટા નથી',
])

<table class="{{ $tableClass }}">
    <tr>
        <th>પશુ</th>
        <th>જન્મ તારીખ</th>
        <th>વાછરડાનો ટેગ</th>
    </tr>

    @forelse($births as $birth)
    <tr>
        <td>{{ $birth->mother_display_label }}</td>
        <td>{{ $birth->birth_date?->format('d-m-Y') ?? '—' }}</td>
        <td>{{ $birth->calf_birth_tag }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="3">{{ $emptyMessage }}</td>
    </tr>
    @endforelse
</table>
