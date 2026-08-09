<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'BluePeak Fintech' }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="theme-color" content="#0c2053">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('partials.styles')
</head>
<body>
{{ $slot }}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.native-splash-hide')
@include('partials.passkeys')
@include('partials.onesignal-bridge')
@include('partials.native-back-button')
@stack('scripts')
</body>
</html>
