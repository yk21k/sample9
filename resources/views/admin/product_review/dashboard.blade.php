@extends('voyager::master')

@section('content')

<div class="page-content container-fluid">

	<h2>商品審査ダッシュボード</h2>

	<div class="row">

		<div class="col-md-2">
			<div class="panel panel-info">
				<div class="panel-body">
					<h3>{{ $pending }}</h3>
						審査待ち
				</div>
			</div>
		</div>

		<div class="col-md-2">
			<div class="panel panel-warning">
				<div class="panel-body">
					<h3>{{ $reviewing }}</h3>
					審査中
				</div>
			</div>
		</div>

		<div class="col-md-2">
			<div class="panel panel-success">
				<div class="panel-body">
					<h3>{{ $approved }}</h3>
						承認済
				</div>
			</div>
		</div>

		<div class="col-md-2">
			<div class="panel panel-danger">
				<div class="panel-body">
					<h3>{{ $rejected }}</h3>
						拒否
				</div>
			</div>
		</div>

		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-body">
					<h3>{{ $needFix }}</h3>
					修正依頼
				</div>
			</div>
		</div>

	</div>

	<br>
	
	<a href="{{ route('admin.product-review.next') }}" class="btn btn-primary btn-lg">
		次の審査を開始
	</a>
	<a href="{{ route('product.review') }}" class="btn btn-primary btn-lg">
		審査待ち一覧
	</a>

</div>

@stop