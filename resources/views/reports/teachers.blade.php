<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>O'qituvchilar Hisoboti</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin: 0 0 5px 0;
            color: #333;
        }

        .info {
            text-align: right;
            margin-bottom: 10px;
            font-size: 8px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }

        td {
            font-size: 7px;
            line-height: 1.3;
        }

        .text-left {
            text-align: left;
            padding-left: 4px;
        }

        .text-center {
            text-align: center;
        }

        .num-col {
            width: 20px;
        }

        .name-col {
            width: 110px;
            text-align: left;
        }

        .degree-col {
            width: 55px;
            font-size: 6px;
        }

        .faculty-col {
            width: 75px;
            text-align: left;
            font-size: 6px;
        }

        .dept-col {
            width: 75px;
            text-align: left;
            font-size: 6px;
        }

        .test-col {
            width: 48px;
            font-weight: bold;
            font-size: 6px;
        }

        .portfolio-col {
            width: 40px;
            font-weight: bold;
            background-color: #f3e5f5;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .entry-header {
            background-color: #66BB6A !important;
        }

        .exit-header {
            background-color: #42A5F5 !important;
        }

        .portfolio-header {
            background-color: #AB47BC !important;
        }
    </style>
</head>
<body>
<h1>O'QITUVCHILAR HISOBOTI</h1>
<div class="info">
    <strong>Sana:</strong> {{ $date }}
</div>

<table>
    <thead>
    <tr>
        <th class="num-col">№</th>
        <th class="name-col">F.I.O</th>
        <th class="degree-col">Ilmiy<br>daraja</th>
        <th class="faculty-col">Fakultet</th>
        <th class="dept-col">Kafedra</th>

        @foreach($entryTests as $test)
            <th class="test-col entry-header">
                {{ $test->title }}<br>
                <small>(Kiruvchi)</small>
            </th>
        @endforeach

        @foreach($exitTests as $test)
            <th class="test-col exit-header">
                {{ $test->title }}<br>
                <small>(Chiquvchi)</small>
            </th>
        @endforeach

        <th class="portfolio-col portfolio-header">
            Portfolio<br>
            <small>(Umumiy)</small>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($teachers as $index => $teacher)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="name-col text-left">
                <strong>{{ $teacher['full_name'] }}</strong>
            </td>
            <td class="degree-col">{{ $teacher['scientific_degree'] }}</td>
            <td class="faculty-col text-left">{{ $teacher['faculty'] }}</td>
            <td class="dept-col text-left">{{ $teacher['department'] }}</td>

            @foreach($teacher['entry_results'] as $result)
                <td class="test-col">{!! nl2br($result) !!}</td>
            @endforeach

            @foreach($teacher['exit_results'] as $result)
                <td class="test-col">{!! nl2br($result) !!}</td>
            @endforeach

            <td class="portfolio-col">
                <strong>{{ $teacher['portfolio_score'] ?: '0' }}</strong>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
