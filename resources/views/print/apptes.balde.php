{{-- <x-layout-print
:customer="$invoice->sale->customer->name"
:address="$invoice->sale->customer->address"
:created="$invoice->sale->transaction_date"
:total="$invoice->sale->total_amount"
:chasier="$invoice->sale->user->name"
:type="'Sale'"
>
    <tbody>
        @php
        $maxRows = 5;
        $count = $invoice->sale->saleDetails->count();
        @endphp
    
        @foreach ($invoice->sale->saleDetails as $item)
        <tr class="bg-[#f7f5f300] text-center h-6">
            <td class="border border-[#daa52000] p-0 text-start py-0">{{ $item->quantity }}</td>
<td class="border border-[#daa52000] p-0 text-start py-0">
    <div class="flex justify-around items-center">
        {{ $item->product->name }}, {{ $item->product->karat->karat }}-{{
                    $item->product->karat->rate .'%' }}, {{ $item->weight }}
        <img src="https://api.thepalacejeweler.com/upload/article/1724140259-igs%206%20Agustus%20rev-04.jpg"
            alt="" srcset="" width="150">
    </div>
</td>
<td class="border border-[#daa52000] p-0 text-start py-0">{{ $item->weight }}</td>
<td class="border border-[#daa52000] p-0 text-start py-0">{{
                number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
<td class="border border-[#daa52000] p-0 text-start py-0">{{ number_format($item->subtotal, 0, ',', '.')
                }}</td>
</tr>
@endforeach

Tambahin baris kosong kalau kurang dari 5
@for ($i = 0; $i < $maxRows - $count; $i++) <tr class="bg-[#f7f5f300] text-center h-6">
    <td class="border border-[#daa52000] p-0 text-start py-0">&nbsp;</td>
    <td class="border border-[#daa52000] p-0 text-start py-0"></td>
    <td class="border border-[#daa52000] p-0 text-start py-0"></td>
    <td class="border border-[#daa52000] p-0 text-start py-0"></td>
    <td class="border border-[#daa52000] p-0 text-start py-0"></td>
    </tr>
    @endfor
    </tbody>
    </x-layout-print> --}}

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display&family=Roboto&display=swap"
            rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Document</title>
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

            .stempel-nota {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100px;
                transform: translate(-50%, -50%) rotate(-20deg);
                opacity: 10;
                z-index: 0;
                pointer-events: none;
            }

            @media print {
                @page {
                    size: 21.6cm 11cm;
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

    <body style="position: relative;">

        <div class="container mb-4" style="margin-top: 3.9cm; margin-left: 8.2cm;">
            <div class="row ">
                <div class="col">
                    <span class="ms-4">{{ $invoice->sale->customer->name }}</span>
                </div>
                <div class="col ">
                    <span class="ms-5">{{ $invoice->sale->customer->address }}</span>
                </div>
            </div>
        </div>

        <table class="table" style="margin-left: 5cm; margin-top:2.3rem">
            <tbody>
                @php
                $maxRows = 5;
                $count = $invoice->sale->saleDetails->count();
                @endphp

                {{-- Data dari sale details --}}
                @foreach ($invoice->sale->saleDetails as $item)
                <tr>
                    <td class="text-center border-0 py-1 ">{{ $item->quantity }}</td>
                    <td class="border-0 py-1 " style="width: 45%;">{{ $item->product->name }}</td>
                    <img src="{{ asset('logo-cetak.png') }}" alt="Stempel" class="stempel-nota">
                    {{-- <img src="{{ asset('logo-cetak.png') }}" alt="Stempel" class="stempel-nota"> --}}
                    <td class="border-0 py-1 text-start">Rp.{{ number_format($item->product->karat->buy_price, 0, ',',
                    '.') }}</td>
                </tr>
                @endforeach

                {{-- Tambahkan baris kosong jika kurang dari maxRows --}}
                @for ($i = 0; $i < $maxRows - $count; $i++) <tr>
                    <td class="text-center border-0 py-1 text-white">0</td>
                    <td class="border-0 py-1 text-white" style="width: 45%;">Nama</td>
                    <td class="border-0 py-1 text-white text-start">Rp.0000</td>
                    </tr>
                    @endfor
                    {{-- <tr>
                    <td class=""><span class="text">Bangko, </span>{{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM ') }}</td>
                    </tr> --}}
                    <tr class="text-start">
                        <th style="width: 13%" class="py-1 border-0 text-start"><span class="">Bangko, </span></th>
                        <td colspan="1" class=" py-1 border-0">{{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM ') }} <span>{{ \Carbon\Carbon::parse($invoice->created)->format('y') }}</span></td>
                        <td class="py-1 border-0">
                            <span class="fw-bold">{{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        </td>
                    </tr>

                    {{-- Baris footer: tanggal dan total amount --}}
                    {{-- <tr>
                    <td class="border-0 py-1 bg-info" style="width: 45%;">
                        {{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM Y') }}
                    </td>
                    <td colspan="2" class="border-0 py-1 bg-danger text-start">
                        Rp.{{ number_format($invoice->total_amount, 0, ',', '.') }}
                    </td>
                    </tr> --}}
            </tbody>
            {{-- <tbody >
            @php
            $maxRows = 5;
            $count = $invoice->sale->saleDetails->count();
            @endphp

            @foreach ($invoice->sale->saleDetails as $item)
            <tr>
                <td class="text-center border-0 py-1 bg-warning" >{{ $item->quantity }}</td>
            <td style="width: 45% " class="border-0 py-1 bg-info">{{ $item->product->name }}</td>
            <td class="border-0 py-1 mx-3 bg-danger" style="text-align: start !important;">Rp.{{ number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @for ($i = 0; $i < $maxRows - $count; $i++) <tr>
                <td class="text-center border-0 py-1 text-white">0</td>
                <td style="width: 45% " class="border-0 py-1 text-white">Nama</td>
                <td class="border-0 py-1 text-white" style="text-align: start !important;">Rp.0000m0</td>
                </tr>
                @endfor
                <tr>
                    <td style="width: 45% " class="border-0 py-1 bg-info">
                        {{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM
                    Y') }}
                    </td>
                    <td class="border-0 py-1 mx-3 bg-danger" style="text-align: start !important;">Rp.{{
                    number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                </tbody> --}}
                {{-- <tfoot class="border-0">
            <tr>
                <td class="border-0" colspan="1">
                    Bangko, {{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM
                    Y') }}
                </td>
                <td class="border-0">
                    {{ number_format($invoice->sale->total_amount, 0, ',', '.') }}
                </td>
                </tr>
                </tfoot> --}}
        </table>

        {{-- <div class="container-fluid" style="margin-left: 5cm;">
        <div class="row">
            <div class="col-2 ">
                <p class="mb-1">CS: {{ $invoice->sale->user->name }}</p>
        <p class="mb-0">
            {{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM Y') }}
        </p>


        <p class="mb-0">
            {{ \Carbon\Carbon::parse($invoice->created)->format('H:i') }}
        </p>
        </div>
        <div class="col-8 pt-4 ps-5">
            <strong style="font-style:italic;" class="mt-3"> {{ spelledOut($invoice->total_amount) }}</strong>
        </div>
        </div>
        </div> --}}

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js">
        </script>
        <script>
            window.addEventListener('load', function() {
                window.print();
                window.onafterprint = function() {
                    history.back();
                };
            });
        </script>
    </body>

    </html>