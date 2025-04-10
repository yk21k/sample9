@extends('layouts.app')

@section('content')
<head>
    <title>Company Profile - Celebration</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f9f3e7;
            color: #5d3f29;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            font-size: 3em;
            color: #d36f6f;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        h3, h4 {
            font-size: 1.5em;
            color: #4d4d4d;
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .link {
            font-size: 1.2em;
            text-decoration: none;
            color: #d36f6f;
            border: 2px solid #d36f6f;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .link:hover {
            background-color: #d36f6f;
            color: white;
        }
        .celebration {
            background-color: #f0e6d2;
            padding: 20px;
            border-radius: 8px;
            font-size: 1.4em;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>🎉 Congratulations on Your Business! 🎉</h1>

        <div class="celebration">
            <h3>Business Name: {{ $parts->name }}</h3>
            <p><strong>Business Description:</strong> {{ $parts->description }}</p>
            <p><strong>Contact Method:</strong> Inquiry from within the site</p>
            <p>
                <a class="link" href="{{ route('inquiries.create', ['id' => $parts->id]) }}">Contact Us</a>
            </p>
            <p><strong>Contact Shop Manager:</strong> {{ $parts->manager }}</p>
        </div>
    </div>

</body>
@endsection
