@extends('layouts.app')
@section('content')
<head>
<title>Page Title</title>
</head>
<body>

<h1>Company Profile</h1><br>
<p><h3>Business name: {{ $parts->name }}</h3></p><br>
<p><h3>Business description: {{ $parts->description }}</h3></p><br>
<p><h3>Contact method: Inquiry from within the site </h3>
	<a class="" href="{{ route('inquiries.create', ['id'=>$parts->id]) }}"></a>
</p><br>
<p><h4>Contact Shop Manager</h4></p>


</body>
@endsection