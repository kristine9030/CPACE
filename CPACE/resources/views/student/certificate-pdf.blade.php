<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
        }
        .frame {
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            padding: 40px;
            border: 10px solid #7B1D1D;
            text-align: center;
        }
        .placeholder-tag {
            display: inline-block;
            background: #fdeaea;
            color: #7B1D1D;
            border: 1px solid #7B1D1D;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 4px 14px;
            border-radius: 12px;
            margin-bottom: 26px;
        }
        .brand {
            font-size: 13px;
            letter-spacing: 3px;
            color: #7B1D1D;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .title {
            font-size: 34px;
            font-weight: bold;
            color: #14283E;
            margin-bottom: 18px;
        }
        .lead {
            font-size: 13px;
            color: #555;
        }
        .name {
            font-size: 28px;
            font-weight: bold;
            color: #7B1D1D;
            margin: 14px 0;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding: 0 30px 6px;
        }
        .body-text {
            font-size: 13px;
            color: #444;
            max-width: 560px;
            margin: 0 auto 24px;
            line-height: 1.6;
        }
        table.subjects {
            margin: 0 auto 28px;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.subjects th, table.subjects td {
            border: 1px solid #ddd;
            padding: 6px 14px;
        }
        table.subjects th {
            background: #f4f5f7;
            color: #333;
        }
        .footer {
            font-size: 10.5px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="placeholder-tag">PLACEHOLDER &mdash; OFFICIAL DESIGN IN PROGRESS</div>
        <div class="brand">C P A C E &middot; C P A R E V I E W E R</div>
        <div class="title">Certificate of Completion</div>
        <div class="lead">This is to certify that</div>
        <div class="name">{{ $student->name }}</div>
        <div class="body-text">
            has successfully completed all six (6) subject mock examinations of the
            CPACE CPA Licensure Examination review program.
        </div>

        <table class="subjects">
            <tr>
                <th>Subject</th>
                <th>Completed</th>
            </tr>
            @foreach ($subjects as $row)
                <tr>
                    <td>{{ $row['name'] }} ({{ $row['code'] }})</td>
                    <td>{{ $row['submitted_at']?->format('M j, Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </table>

        <div class="footer">
            Issued {{ $earnedAt?->format('F j, Y') ?? now()->format('F j, Y') }} &mdash;
            this document is a system-generated placeholder and is not a final, signed certificate.
        </div>
    </div>
</body>
</html>
