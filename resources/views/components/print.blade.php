@props([
'name'=>'',
'address'=>'',
'details'=>'',
'date'=>'',
'cs'=>'',
'total'=>0,
'images' => ''
])


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}" type="image/x-icon">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
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

        .watermark-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .watermark {
            width: 80px;
            height: auto;
        }

        .table-container {
            position: relative;
        }

        .table-content {
            position: relative;
            z-index: 1;
        }

        @media print {
            @page {
                size: legal portrait;
                margin: 0;
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

            .watermark {
                /* opacity: 0.1; */
                width: 60px;
                height: auto;
            }
        }
    </style>
</head>

<body>

    <div class="container mb-4" style="margin-top: 3.8cm; margin-left: 8cm;">
        <div class="row ">
            <div class="col">
                <span class="ms-5">{{ $name }}</span>
            </div>
            <div class="col text-end">
                <span class="ms-5">{{ $address }}</span>
            </div>
        </div>
    </div>
    <div class="table-container">
        <div class="watermark-container">
            @if ($images)
            @foreach ($images as $img)
                <img src="{{ asset('storage/'. $img) }}" alt="" class="watermark">
            @endforeach
            @endif
        </div>

        <div class="table-content">
            <table class="table" style="margin-left: 5cm; margin-top:2.4rem;">
                <tbody>
                    @php
                    $maxRows = 5;
                    $count = $details;
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
                                        <div style="width: 38%"
                                            class="d-flex justify-content-between align-items-center">
                                            <span style="font-size: 14px" class="text-white"
                                                style="color: #ffffff00"><span
                                                    style="color: transparent">Bangko,</span></span>
                                            <span style="font-size: 14px" class="mx-1">{{
                                                \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D
                                                MMMM') }}</span>
                                        </div>
                                        <div class="ms-5">
                                            <span class="ms-3" style="font-size: 14px"><span
                                                    style="color: transparent">20</span>
                                                {{\Carbon\Carbon::parse($date)->format('y') }}</span>
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
                                            \Carbon\Carbon::parse($date)->locale('id')->isoFormat('HH:mm:ss')
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

 <script>
    // Auto print on load
    window.addEventListener('load', function() {
        window.print();
        window.onafterprint = function() {
                window.close();
        };
        // Close halaman jika klik tombol batal
    });
</script>
</body>

</html>