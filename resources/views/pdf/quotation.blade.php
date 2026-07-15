<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        @page { margin: 24px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
        }
        .header {
            background-color: rgb(212, 212, 212);
            padding: 4px 14px 4px;
            margin-bottom: 2px;
        }
        .header table { margin: 0 auto; border-collapse: collapse; }
        .header .logo-cell { width: 100px; vertical-align: middle; }
        .header .logo { width: 90px; height: 90px; }
        .header .text-cell { vertical-align: middle; text-align: left; }
        .header .company { font-size: 24px; font-weight: bold; letter-spacing: 1px; color: #92868a; }
        .header .tagline { font-size: 14px; letter-spacing: 1px; color: #8ea2aa; }
        .doc-title { text-align: center; font-size: 12px; font-weight: bold; letter-spacing: 1px; margin: 6px 0 12px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { padding: 2px 0; vertical-align: top; font-size: 10px; }
        .meta .r { text-align: right; }
        .meta .label { color: #111; }

        .intro { margin: 6px 0 10px; }
        .intro p { margin: 3px 0; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items th, table.items td {
            border: 1px solid #444;
            padding: 5px 6px;
            font-size: 10px;
        }
        table.items th { background: #fff; font-weight: bold; text-align: center; }
        td.num { text-align: center; }
        td.money { text-align: right; }
        td.room { color: #dc2626; font-weight: bold; text-align: center; }
        .design-name { font-weight: bold; }
        .design-finish { color: #444; font-size: 9px; }
        .thumb { width: 90px; height: 70px; object-fit: cover; }
        .thumb-empty { display: block; width: 90px; height: 70px; background: #f1f5f9; }

        table.totals { width: 42%; border-collapse: collapse; margin-left: auto; margin-top: -1px; }
        table.totals td { border: 1px solid #444; padding: 5px 6px; color: #dc2626; }
        table.totals td.tl { font-weight: bold; }
        table.totals td.tv { text-align: right; }

        .terms { margin-top: 16px; font-size: 8px; color: #333; }
        .terms .title { font-weight: bold; margin-bottom: 3px; }
        .terms ol { margin: 0; padding-left: 16px; }
        .terms li { margin-bottom: 1px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo-cell">
                    @if ($logo)
                        <img class="logo" src="{{ $logo }}" alt="">
                    @endif
                </td>
                <td class="text-cell">
                    <div class="company">{{ $company['company_name'] }}</div>
                    <div class="tagline">{{ $company['tagline'] }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="doc-title">QUOTATION</div>

    <table class="meta">
        <tr>
            <td class="label">Client Name: {{ $client['name'] }}</td>
            <td class="r label">Date: {{ $date }}</td>
        </tr>
        <tr>
            <td class="label">Contact Number: {{ $client['contact'] }}</td>
            <td class="r label">Sales Manager: {{ $salesManager['name'] }}</td>
        </tr>
        <tr>
            <td class="label">@if ($architectName)Architect Name: {{ $architectName }}@endif</td>
            <td class="r label">Contact No: {{ $salesManager['contact'] }}</td>
        </tr>
    </table>

    <div class="intro">
        <p>Dear Sir,</p>
        <p>Below are the best quoted rates for the material you have selected for your prestigious project.</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:6%">Sr.No</th>
                <th style="width:11%">Size</th>
                <th style="width:21%">Design {{ $mode === 'code' ? 'Code' : 'Name' }}</th>
                <th style="width:20%">Image</th>
                <th style="width:10%">Sq.Ft Rate</th>
                <th style="width:11%">Rate Pcs</th>
                <th style="width:10%">No Of QTY</th>
                <th style="width:11%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rooms as $room)
                @unless ($room['flat'])
                    <tr>
                        <td class="num">{{ $room['sr'] }}</td>
                        <td class="room" colspan="7">{{ $room['name'] }}</td>
                    </tr>
                @endunless
                @foreach ($room['items'] as $item)
                    <tr>
                        <td class="num">{{ $item['sr'] }}</td>
                        <td class="num">{{ $item['size'] }}</td>
                        <td>
                            <span class="design-name">{{ $item['name'] }}</span>
                            @if ($item['finish'])<br><span class="design-finish">{{ $item['finish'] }}</span>@endif
                        </td>
                        <td class="num">
                            @if ($item['image'])
                                <img class="thumb" src="{{ $item['image'] }}" alt="">
                            @else
                                <span class="thumb-empty"></span>
                            @endif
                        </td>
                        <td class="money">{{ number_format($item['sqft_rate'], 2) }}</td>
                        <td class="money">{{ number_format($item['rate_pcs'], 2) }}</td>
                        <td class="num">{{ $item['qty'] }}@if ($item['per_box'] !== null) ({{ $item['per_box'] }})@endif</td>
                        <td class="money">{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="8" class="num">No products.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="tl">Freight Extra</td>
            <td class="tv">{{ number_format($totals['freight'], 2) }} {{ $company['currency'] }}</td>
        </tr>
        <tr>
            <td class="tl">Paid</td>
            <td class="tv">{{ number_format($totals['paid'], 2) }} {{ $company['currency'] }}</td>
        </tr>
        <tr>
            <td class="tl">Payable</td>
            <td class="tv">{{ number_format($totals['payable'], 2) }} {{ $company['currency'] }}</td>
        </tr>
        <tr>
            <td class="tl">Gr. Total</td>
            <td class="tv">{{ number_format($totals['grand_total'], 2) }} {{ $company['currency'] }}</td>
        </tr>
    </table>

    <div class="terms">
        <div class="title">Terms &amp; Conditions:</div>
        <ol>
            @foreach ($company['terms'] as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ol>
    </div>
</body>
</html>
