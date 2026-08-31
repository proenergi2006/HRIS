<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $letter->title }} — {{ $letter->employee->name }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 0; }
  .page { padding: 30px 40px; }
  .letterhead { border-bottom: 2px solid #0F2A4A; padding-bottom: 10px; margin-bottom: 20px; }
  .letterhead h1 { margin: 0; font-size: 15px; color: #0F2A4A; }
  .letterhead p  { margin: 2px 0 0; font-size: 9px; color: #555; }
  .meta { margin-bottom: 18px; font-size: 10.5px; }
  .meta .number { font-weight: bold; }
  .title { text-align: center; font-weight: bold; font-size: 13px; text-decoration: underline; margin: 18px 0; text-transform: uppercase; }
  .content { font-size: 11px; line-height: 1.7; white-space: pre-wrap; text-align: justify; }
  .sign-block { margin-top: 40px; width: 220px; margin-left: auto; text-align: center; }
  .sign-line { border-top: 1px solid #333; margin: 55px 0 4px; }
</style>
</head>
<body>
<div class="page">

  @include('components.pdf-kop', ['company' => $letter->employee->company ?? config('sipro.company.code')])

  <div class="meta">
    <div class="number">No: {{ $letter->letter_number }}</div>
  </div>

  <div class="title">{{ $letter->title }}</div>

  <div class="content">{{ $letter->body }}</div>

  <div class="sign-block">
    <div>{{ $letter->employee->company?->name ?? '' }},</div>
    <div>{{ $letter->issued_date?->format('d F Y') }}</div>
    <div class="sign-line"></div>
    <div>{{ $letter->issuedBy?->name ?? 'HRD' }}</div>
  </div>

</div>
</body>
</html>
