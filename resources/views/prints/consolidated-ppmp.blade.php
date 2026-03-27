<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidated PPMP Print</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #e6e6e6;
            color: #000;
            font-family: "Times New Roman", Times, serif;
        }

        .print-toolbar {
            width: 100%;
            max-width: 980px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-toolbar a,
        .print-toolbar button {
            border: 1px solid #111;
            background: #fff;
            color: #111;
            padding: 10px 16px;
            font: inherit;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .sheet {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 22px 18px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
        }

        .heading {
            text-align: center;
            margin-bottom: 10px;
        }

        .heading h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .heading p {
            margin: 2px 0;
            font-size: 16px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin: 4px 0 6px;
            font-size: 13px;
        }

        .meta-label {
            font-weight: 700;
        }

        .write-line {
            display: inline-block;
            min-width: 150px;
            min-height: 14px;
            margin-left: 6px;
            border-bottom: 1px solid #000;
            vertical-align: baseline;
        }

        .form-table,
        .items-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .form-table td,
        .items-table th,
        .items-table td,
        .signature-table td {
            border: 1px solid #000;
        }

        .form-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 13px;
        }

        .field-label {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .field-value {
            font-size: 13px;
            min-height: 18px;
            font-weight: 700;
        }

        .office-value {
            min-height: 56px;
            display: flex;
            align-items: flex-end;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .page-value {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        .items-table thead th {
            padding: 6px 4px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            vertical-align: middle;
        }

        .items-table td {
            padding: 5px 6px;
            font-size: 12px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .status-cell {
            font-weight: 700;
            text-transform: uppercase;
        }

        .total-row td {
            font-weight: 700;
        }

        .signature-table td {
            width: 50%;
            padding: 10px 12px 12px;
            height: 126px;
            vertical-align: top;
            font-size: 12px;
        }

        .signature-heading {
            margin-bottom: 26px;
            font-weight: 700;
        }

        .signature-entry {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 8px;
        }

        .signature-label {
            width: 92px;
            flex: 0 0 92px;
            font-weight: 700;
        }

        .signature-line {
            flex: 1 1 auto;
            border-bottom: 1px solid #000;
            min-height: 26px;
        }

        .signature-text {
            flex: 1 1 auto;
            min-height: 18px;
        }

        .signature-text.strong {
            font-weight: 700;
            text-transform: uppercase;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .sheet {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
@php
    $generatedAt = $generatedAt instanceof \Illuminate\Support\Carbon ? $generatedAt : \Illuminate\Support\Carbon::parse($generatedAt);
    $preparedBy = $generatedBy ? \Illuminate\Support\Str::upper($generatedBy) : '________________';
@endphp

<div class="print-toolbar">
    <a href="javascript:window.close()">Close</a>
    <button type="button" onclick="window.print()">Print</button>
</div>

<div class="sheet">
    <div class="heading">
        <h1>CONSOLIDATED PPMP</h1>
        <p>University of Northern Philippines</p>
        <p>Vigan City, Ilocos Sur</p>
    </div>

    <div class="meta-row">
        <div><span class="meta-label">Entity Name:</span> UNIVERSITY OF NORTHERN PHILIPPINES</div>
        <div><span class="meta-label">Fund Cluster:</span> {{ $fundCluster }}</div>
    </div>

    <table class="form-table">
        <tr>
            <td rowspan="2" style="width: 34%;">
                <span class="field-label">Office/Section:</span>
                <div class="office-value">CONSOLIDATED PROCUREMENT MANAGEMENT PLAN</div>
            </td>
            <td style="width: 28%;">
                <span class="field-label">Report No.:</span>
                <div class="field-value">{{ $reportNumber }}</div>
            </td>
            <td style="width: 12%;">
                <span class="field-label">Page</span>
                <div class="page-value">1</div>
            </td>
            <td style="width: 26%;">
                <span class="field-label">Date:</span>
                <div class="field-value">{{ $generatedAt->format('F d, Y') }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="field-label">Prepared By:</span>
                <div class="field-value">{{ $generatedBy }}</div>
            </td>
            <td>
                <span class="field-label">Total PPMP:</span>
                <div class="field-value">{{ $totalPpmps }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">No.</th>
                <th style="width: 34%;">Department / Unit</th>
                <th style="width: 28%;">Fund Source</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Total PPMP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->department_name }}</td>
                    <td>{{ $row->fund_source_name }}</td>
                    <td class="center status-cell">{{ $row->status }}</td>
                    <td class="center">{{ $row->total_ppmps }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">No consolidated PPMP data found.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="right">TOTAL:</td>
                <td class="center">{{ $totalPpmps }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-heading">Prepared by:</div>
                <div class="signature-entry">
                    <span class="signature-label">Signature:</span>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-entry">
                    <span class="signature-label">Printed Name:</span>
                    <span class="signature-text strong">{{ $preparedBy }}</span>
                </div>
                <div class="signature-entry">
                    <span class="signature-label">Designation:</span>
                    <span class="signature-text">ADMIN</span>
                </div>
            </td>
            <td>
                <div class="signature-heading">Noted by:</div>
                <div class="signature-entry">
                    <span class="signature-label">Signature:</span>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-entry">
                    <span class="signature-label">Printed Name:</span>
                    <span class="signature-text strong">________________</span>
                </div>
                <div class="signature-entry">
                    <span class="signature-label">Designation:</span>
                    <span class="signature-text">________________</span>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
