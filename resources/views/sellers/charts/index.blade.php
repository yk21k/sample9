@extends('layouts.seller')

@section('content')
<h1>Charts Page</h1>
<!-- ユーザー数の推移グラフ -->
<canvas id="userChart" width="400" height="200"></canvas>

<!-- オーダー数の推移グラフ -->
<canvas id="orderChart" width="400" height="200"></canvas>


<script>
        // ユーザー数の推移グラフ
        var userCounts = @json($userCounts);
        var userLabels = userCounts.map(function(item) { return item.month; });
        var userData = userCounts.map(function(item) { return item.count; });

        var ctx1 = document.getElementById('userChart').getContext('2d');
        var userChart = new Chart(ctx1, {
            type: 'line', // グラフの種類（線グラフ）
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'ユーザー数の推移',
                    data: userData,
                    borderColor: 'rgb(44, 47, 242)',
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { 
                        title: {
                            display: true,
                            text: '月'
                        },
                        ticks: {
		                    color: 'white' // x軸の目盛り線の色を白に設定
		                },
		                grid: {
		                    color: 'white' // x軸のグリッド線の色を白に設定
		                }
                    },
                    y: { 
                        title: {
                            display: true,
                            text: 'ユーザー数'
                        },
                        ticks: {
		                    color: 'white' // x軸の目盛り線の色を白に設定
		                },
		                grid: {
		                    color: 'white' // x軸のグリッド線の色を白に設定
		                }
                    }
                },

	            plugins: {
		            legend: {
		                labels: {
		                    color: 'white' // 凡例（label）の文字色を白に設定
		                }
		            }
        		}    
            }
            
        });

        // オーダー数の推移グラフ
        var orderCounts = @json($subOrderCounts);
        var orderLabels = orderCounts.map(function(item) { return item.month; });
        var orderData = orderCounts.map(function(item) { return item.count; });

        var ctx2 = document.getElementById('orderChart').getContext('2d');
        var orderChart = new Chart(ctx2, {
            type: 'line', // グラフの種類（線グラフ）
            data: {
                labels: orderLabels,
                datasets: [{
                    label: 'オーダー数の推移',
                    data: orderData,
                    borderColor: 'rgb(255, 99, 132)',
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '月'
                        },
                        ticks: {
		                    color: 'white' // x軸の目盛り線の色を白に設定
		                },
		                grid: {
		                    color: 'white' // x軸のグリッド線の色を白に設定
		                }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'オーダー数'
                        },
                        ticks: {
		                    color: 'white' // x軸の目盛り線の色を白に設定
		                },
		                grid: {
		                    color: 'white' // x軸のグリッド線の色を白に設定
		                }
                    }
                },
                plugins: {
		            legend: {
		                labels: {
		                    color: 'white' // 凡例（label）の文字色を白に設定
		                }
		            }
        		}    
            }
        });


</script>
@endsection