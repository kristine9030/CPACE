<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: 297mm 210mm landscape; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        @font-face {
            font-family: 'MontserratBold';
            src: url(data:font/ttf;base64,{{ $boldFont }}) format('truetype');
            font-weight: 700;
        }
        @font-face {
            font-family: 'MontserratRegular';
            src: url(data:font/ttf;base64,{{ $regularFont }}) format('truetype');
            font-weight: 400;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
        }
        .bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Positions calibrated against the template's printed underline
           (~48.8% down) and "Date Completed:" label (~78% down, ending
           at ~37.8% across) - see CertificateController for the source image. */
        .name-box {
            position: absolute;
            left: 22.1%;
            width: 48.6%;
            top: 39%;
            height: 9.6%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
        }
        .name {
            font-family: 'MontserratBold', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1pt;
            color: #7B1D1D;
            white-space: nowrap;
        }
        .date-box {
            position: absolute;
            left: 39%;
            top: 77.1%;
            height: 3.4%;
            display: flex;
            align-items: center;
        }
        .date {
            font-family: 'MontserratRegular', sans-serif;
            font-weight: 400;
            font-size: 12pt;
            color: #222;
        }
    </style>
</head>
<body>
    <div class="page">
        <img class="bg" src="data:image/png;base64,{{ $templateImage }}" alt="">

        <div class="name-box">
            <div class="name" style="font-size: {{ $nameFontSize }}pt;">{{ $fullNameUpper }}</div>
        </div>

        <div class="date-box">
            <div class="date">{{ $earnedAtLabel }}</div>
        </div>
    </div>
</body>
</html>
