@props([
    'name'=>'',
    'address'=>'',

    'details'=>'',
    'date'=>'',
    'cs'=>'',
    'total'=>0
])


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('logo-cetak.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display&family=Roboto&display=swap"
        rel="stylesheet" />
    <title>Cetak Nota</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            text-orientation: mixed;
        }

        @media print {
            @page {
                /*size: 21.6cm 11cm;*/
                size: legal portrait;
                /* Lebar x Tinggi */
                margin: 0;
                /* Optional, bisa diatur sesuai layout */
                padding: 0;
            }

            body {
                font-size: 12pt;
                color: black;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container mb-4" style="margin-top: 3.9cm; margin-left: 8cm;">
        <div class="row ">
            <div class="col">
                <span class="ms-5">{{ $name }}</span>
            </div>
            <div class="col text-end">
                <span class="ms-5">{{ $address }}</span>
            </div>
        </div>
    </div>

    <table class="table" style="margin-left: 5cm; margin-top:2.4rem">
        <tbody>
            @php
            $maxRows = 5;
            $count = $details->count();
            @endphp

            {{-- Loop data yang ada --}}
            {{ $slot }}
            
            {{-- Loop baris kosong jika kurang dari 5 --}}
                @for ($i = 0; $i < $maxRows - $count; $i++) <tr>
                <td class="text-center border-0 py-1">&nbsp;</td>
                <td class="border-0 py-1"></td>
                <td class="border-0 py-1 mx-3"></td>
                </tr>
                @endfor

                <tr>
                    <td class="py-1 border-0" colspan="3">
                        <div class="d-flex align-items-center">
                            <div class="col-6 d-flex align-items-center">
                                <div style="width: 38%" class="d-flex justify-content-between align-items-center">
                                    <span style="font-size: 14px" class="text-white" style="color: transparent">Bangko,</span>
                                    <span style="font-size: 14px" class="mx-1">{{
                                        \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D
                                        MMMM') }}</span>
                                </div>
                                <div class="ms-5">
                                    <span class="ms-3" style="font-size: 14px"><span style="color: transparent">20</span> {{\Carbon\Carbon::parse($date)->format('y') }}</span>
                                </div>
                            </div>
                            <div class="col-6 ms-5">
                                <strong class="mx-4">{{ number_format($total, 0, ',', '.')
                                    }}</strong>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="py-1 border-0" colspan="3"></td>
                </tr>

                <tr>
                    <td class="py-2 border-0" colspan="3">
                        <div class="row">
                            <div class="col-2">
                                <p class="mb-1 ps-3">
                                    <strong>CS:</strong> {{ $cs }}
                                </p>
                                <p class="mb-1 ps-3">
                                    {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D
                                    MMMM Y') }}
                                </p>
                                <p class="mb-0 ps-3">
                                    {{
                                    \Carbon\Carbon::parse($date)->locale('id')->isoFormat('HH:mm')
                                    }}
                                </p>
                            </div>
                            <div class="col-10">
                                <h5 class="fow-bold mt-1" style="font-style: italic;">
                                    {{ spelledOut($total) }}
                                </h5>
                            </div>
                        </div>
                    </td>
                </tr>
        </tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener('load', function () {
            window.print(); 
            window.onafterprint = function () {
              window.close();
            };
          });
    </script>

</body>

</html>