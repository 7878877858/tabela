@extends('layouts.app')

@section('title','દૈનિક સ્ટાફ કાર્ય અહેવાલ')

@section('content')

<style>
    .report-wrapper {
        width: 100%;
        overflow-x: auto;
        background: #fff;
        border: 2px solid #4f63d8;
        border-radius: 8px;
        padding: 10px;
        margin: auto;
    }

    .report-content {
        min-width: 1000px;
        /* 1200 ના બદલે */
    }

    /* ================= HEADER ================= */

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        vertical-align: top;
    }

    .report-title {
        text-align: center;
    }

    .report-title h1 {
        margin: 0;
        color: #b30000;
        font-size: 42px;
        font-weight: 700;
        line-height: 1.1;
    }

    .report-title h3 {
        margin-top: 5px;
        color: #173b82;
        font-size: 18px;
        font-weight: 600;
    }

    /* ================= TOP INFO ================= */

    .top-info {
        font-size: 15px;
        font-weight: 600;
        line-height: 2;
        color: #222;
    }

    /* ================= SUMMARY ================= */

    .summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin-top: 12px;
    }

    .summary-box {
        background: #fff;
        min-height: 95px;
        text-align: center;
        border-radius: 8px;
        padding: 8px;
    }

    .summary-box .icon {
        font-size: 22px;
        display: block;
        margin-bottom: 5px;
    }

    .summary-box h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        color: #1f2937;
    }

    .summary-box p {
        margin: 5px 0 0;
        font-size: 14px;
    }

    /* Box Colors */

    .summary-blue {
        border: 2px solid #2563eb;
    }

    .summary-green {
        border: 2px solid #16a34a;
    }

    .summary-orange {
        border: 2px solid #ea580c;
    }

    .summary-red {
        border: 2px solid #dc2626;
    }

    .summary-purple {
        border: 2px solid #7c3aed;
    }

    .summary-dark {
        border: 2px solid #1e40af;
    }

    /* ================= SECTION TITLE ================= */

    .section-title {
        margin-top: 14px;
        padding: 10px;
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        color: #173b82;
        background: #e8efff;
        border: 1px solid #4f63d8;
        border-bottom: none;
    }

    /* ================= TABLE ================= */

    .report-table {
        width: 100%;
        border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #999;
        padding: 6px;
        font-size: 13px;
        vertical-align: middle;
    }

    .report-table th {
        background: #eef3ff;
        color: #173b82;
        font-size: 14px;
        font-weight: 700;
        text-align: center;
    }

    .report-table td {
        background: #fff;
    }

    /* ================= 3 COLUMN SECTION ================= */

    .three-col {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .three-col .section-title {
        margin-top: 0;
        font-size: 20px;
    }

    /* ================= 2 COLUMN SECTION ================= */

    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .two-col .section-title {
        margin-top: 0;
        font-size: 20px;
    }

    /* ================= FOOTER ================= */

    .footer-sign {
        margin-top: 20px;
        border-top: 1px solid #ccc;
        padding-top: 15px;
    }

    .footer-sign table {
        width: 100%;
    }

    .footer-sign td {
        text-align: center;
        font-size: 15px;
    }

    /* ================= PRINT ================= */

    @media print {

        body {
            background: #fff;
        }

        .report-wrapper {
            border: none;
            padding: 0;
        }

    }

    /* ================= MOBILE ================= */

    @media(max-width:768px) {

        .report-content {
            min-width: 1000px;
        }

        .report-title h1 {
            font-size: 42px;
        }

        .summary-box h2 {
            font-size: 24px;
        }

    }
</style>

<div class="report-wrapper">

    {{-- HEADER --}}

    <table class="header-table">

        <tr>

            <td width="25%">

                <b>તારીખ :</b>
                {{ date('d-m-Y',strtotime($dailyReport->report_date)) }}

                <br><br>

                <b>શિફ્ટ :</b>
                {{ $dailyReport->shift }}

            </td>

            <td width="50%" class="report-title">

                <h1>દૈનિક સ્ટાફ કાર્ય અહેવાલ</h1>

                <h3>(Daily Staff Work Report)</h3>

            </td>

            <td width="25%" align="right">

                <b>અહેવાલ નં :</b>
                {{ $dailyReport->report_number }}

                <br><br>

                <b>બનાવનાર :</b>
                {{ $dailyReport->reporter ?? '-' }}

            </td>

        </tr>

    </table>

    {{-- SUMMARY --}}

    <table class="summary-table">

        <tr>

            <td>
                <div class="summary-box summary-blue">
                    🐃
                    <h2>{{ $totalAnimals }}</h2>
                    કુલ પશુ
                </div>
            </td>

            <td>
                <div class="summary-box summary-green">
                    🐄
                    <h2>{{ $lactatingAnimals }}</h2>
                    દૂધ આપતા પશુ
                </div>
            </td>

            <td>
                <div class="summary-box summary-orange">
                    🤰
                    <h2>{{ $pregnantAnimals }}</h2>
                    ગર્ભવતી પશુ
                </div>
            </td>

            <td>
                <div class="summary-box summary-red">
                    ❤️
                    <h2>{{ $heatAnimals ?? 0 }}</h2>
                    Heat માં પશુ
                </div>
            </td>

            <td>
                <div class="summary-box summary-purple">
                    🐂
                    <h2>{{ $dryAnimals }}</h2>
                    Dry પશુ
                </div>
            </td>

            <td>
                <div class="summary-box summary-dark">
                    🥛
                    <h2>{{ $totalMilk }}</h2>
                    આજનું કુલ દૂધ
                </div>
            </td>

        </tr>

    </table>

    {{-- SECTION 1 --}}

    <div class="section-title">
        1. સ્ટાફ વિગત અને હાજરી
    </div>

    <table class="report-table">

        <tr>
            <th width="5%">ક્રમ</th>
            <th>સ્ટાફનું નામ</th>
            <th width="15%">હાજરી</th>
            <th>કામ</th>
            <th>નોંધ</th>
        </tr>

        @foreach($dailyReport->staff as $i => $staff)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $staff->employee->name ?? '-' }}</td>
            <td>{{ $staff->status }}</td>
            <td>-</td>
            <td>{{ $staff->remarks }}</td>
        </tr>
        @endforeach

    </table>

    {{-- SECTION 2 --}}

    <div class="section-title">
        2. પશુ વિગત, ઉત્પાદન, ખોરાક અને પ્રજનન માહિતી
    </div>

    <table class="report-table">

        <tr>
            <th>પશુ નં.</th>
            <th>સવાર દૂધ</th>
            <th>સાંજ દૂધ</th>
            <th>કુલ</th>
            <th>AI તારીખ</th>
            <th>પ્રેગ્નન્ટ તારીખ</th>
        </tr>

        @foreach($dailyReport->milk as $milk)
        <tr>
            <td>{{ $milk->buffalo->tag_number ?? '-' }}</td>
            <td>{{ $milk->morning_milk }}</td>
            <td>{{ $milk->evening_milk }}</td>
            <td>{{ $milk->total_milk }}</td>

            <td>
                {{ $milk->buffalo->ai_date ?? '-' }}
            </td>

            <td>
                {{ $milk->buffalo->pregnancy_check_date ?? '-' }}
            </td>
        </tr>
        @endforeach

    </table>


    <table class="report-table">

        <tr>

            <td width="33%" valign="top">

                <div class="section-title"
                    style="color:#d63384">
                    3. આરોગ્ય અને સારવાર
                </div>

                <table class="report-table">
                    <tr>
                        <th width="5%">પશુ નં.</th>
                        <th>સમસ્યા</th>
                        <th>સારવાર</th>
                    </tr>

                    @forelse($dailyReport->health as $health)
                    <tr>
                        <td>{{ $health->buffalo->tag_number ?? '-' }}</td>
                        <td>{{ $health->health_issue }}</td>
                        <td>{{ $health->treatment }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">ડેટા નથી</td>
                    </tr>
                    @endforelse

                </table>

            </td>

            <td width="33%" valign="top">

                <div class="section-title"
                    style="color:#198754">
                    4. બચ્ચા જન્મ વિગત
                </div>

                <table class="report-table">

                    <tr>
                        <th width="5%">પશુ નં.</th>
                        <th>જન્મ તારીખ</th>
                        <th>વાછરડાના ટેગ નં.</th>
                    </tr>

                    @forelse($births as $birth)
                    <tr>
                        <td>{{ $birth->tag_number }}</td>
                        <td>{{ $birth->birth_date }}</td>
                        <td>
                            {{ $birth->calf_tag_number }}
                            ({{ ucfirst($birth->calf_gender) }})
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">ડેટા નથી</td>
                    </tr>
                    @endforelse

                </table>

            </td>

            <td width="33%" valign="top">

                <div class="section-title"
                    style="color:#6f42c1">
                    5. રસી / વેક્સિનેશન
                </div>

                <table class="report-table">

                    <tr>
                        <th>પશુ નં.</th>
                        <th>રસી</th>
                        <th>તારીખ</th>
                        <th>નોંધ</th>
                    </tr>

                    @forelse($dailyReport->vaccinations as $vaccination)

                    <tr>
                        <td>{{ $vaccination->buffalo->tag_number ?? '-' }}</td>

                        <td>{{ $vaccination->vaccine_name }}</td>

                        <td>
                            {{ $vaccination->vaccination_date
            ? \Carbon\Carbon::parse($vaccination->vaccination_date)->format('d-m-Y')
            : '-' }}
                        </td>

                        <td>{{ $vaccination->remarks }}</td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="4" class="text-center">
                            ડેટા નથી
                        </td>
                    </tr>

                    @endforelse

                </table>

            </td>

        </tr>

    </table>

    <table width="100%" cellspacing="8" style="margin-top:10px;">

        <tr>

            <td width="50%" valign="top">

                <div class="section-title" style="color:#173b82;">
                    6. હીટ / AI / ગર્ભ ચેક રિમાઇન્ડર
                </div>

                <table class="report-table">
                    <tr>
                        <th>પશુ નં.</th>
                        <th>હીટ બાકી</th>
                        <th>AI બાકી</th>
                        <th>ગર્ભ ચેક</th>
                    </tr>
                    <tbody>
                        @forelse($dailyReport->pregnancy as $preg)
                        <tr>
                            <td>{{ $preg->buffalo->tag_number ?? '-' }}</td>
                            <td>{{ $preg->buffalo->heat_date }}</td>
                            <td>{{ $preg->buffalo->ai_date }}</td>
                            <td>{{ $preg->buffalo->pregnancy_check_date }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">ડેટા નથી</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>

            </td>

            <td width="50%" valign="top">

                <div class="section-title" style="color:#d97706;">
                    7. ફીડ સ્ટોક વિગત
                </div>

                <table class="report-table">
                    <tr>
                        <th>પશુ નં.</th>
                        <th>ફીડ નામ</th>
                        <th>એકમ</th>
                        <th>જથ્થો</th>
                        <th>ટાઈમ</th>
                    </tr>
                    <tbody>
                        @forelse($dailyReport->feed as $feed)
                        <tr>
                            <td>{{ $preg->buffalo->tag_number ?? '-' }}</td>
                            <td>{{ $feed->feed_name }}</td>
                            <td>{{ $feed->unit }}</td>
                            <td>{{ $feed->quantity }}</td>
                            <td>{{ $feed->feed_time }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">ડેટા નથી</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>

            </td>

        </tr>

    </table>

    @php
    $expenseTotal = $dailyReport->expenses->sum('amount');
    $medicineTotal = $dailyReport->health->sum('medicine_cost');
    $incomeTotal = $dailyReport->incomes->sum('amount');

    $totalExpense = $expenseTotal + $medicineTotal;
    $profitLoss = $incomeTotal - $totalExpense;
@endphp

    <table width="100%" cellspacing="8" style="margin-top:10px;">

        <tr>

            <td width="40%" valign="top">

                <div class="section-title" style="color:#173b82;">
                    8. ખર્ચ અને આવક
                </div>

                <table class="report-table">

    <tr>
        <th>પ્રકાર</th>
        <th>વિગત</th>
        <th>રકમ</th>
    </tr>

    @foreach($dailyReport->expenses as $expense)
    <tr>
        <td>ખર્ચ</td>
        <td>{{ $expense->title }}</td>
        <td>₹ {{ number_format($expense->amount,2) }}</td>
    </tr>
    @endforeach

    @foreach($dailyReport->health as $health)
    <tr>
        <td>દવા ખર્ચ</td>
        <td>{{ $health->health_issue }}</td>
        <td>₹ {{ number_format($health->medicine_cost,2) }}</td>
    </tr>
    @endforeach

    @foreach($dailyReport->incomes as $income)
    <tr>
        <td>આવક</td>
        <td>{{ $income->title }}</td>
        <td>₹ {{ number_format($income->amount,2) }}</td>
    </tr>
    @endforeach

    <tr style="font-weight:bold;">
        <td colspan="2">કુલ ખર્ચ</td>
        <td>₹ {{ number_format($totalExpense,2) }}</td>
    </tr>

    <tr style="font-weight:bold;">
        <td colspan="2">કુલ આવક</td>
        <td>₹ {{ number_format($incomeTotal,2) }}</td>
    </tr>

    <tr style="font-weight:bold;">
        <td colspan="2">
            {{ $profitLoss >= 0 ? 'કુલ નફો' : 'કુલ ખોટ' }}
        </td>
        <td>
            ₹ {{ number_format(abs($profitLoss),2) }}
        </td>
    </tr>

</table>

            </td>

            <td width="60%" valign="top">

    <div class="section-title" style="color:#0f766e;">
        9. ખાસ નોંધ / સૂચના
    </div>

    <div style="
        border:1px solid #999;
        height:200px;
        padding:10px;
        font-size:14px;
    ">
        {!! nl2br(e($dailyReport->notes ?? 'કોઈ નોંધ નથી')) !!}
    </div>

</td>

        </tr>

    </table>
</div>

@endsection