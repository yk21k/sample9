@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h2>商品一括登録（CSV）</h2>
    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="csv_file">CSVファイルを選択</label>
            <input type="file" name="csv_file" class="form-control" required>
        </div>
        <button class="btn btn-primary">アップロードして登録</button>
    </form>
</div>
@endsection
