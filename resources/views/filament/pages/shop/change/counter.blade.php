<x-input-count model="iservice" label="Jasa Pembuatan (Opsional)"/>
<x-input-count model="idiscount" label="Diskon (Opsional)" />
@if ($method!=='online')
<x-input-count model="icash" label="Nominal Pembayaran" />
@endif