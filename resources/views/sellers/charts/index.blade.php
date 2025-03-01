@extends('layouts.seller')

@section('content')
<h1>Charts Page</h1>

<!-- 見出し（タイトル） -->
<h2>ユーザー数(全体{{ $userTotalCount }}名)の推移</h2>
<!-- ユーザー数の推移グラフ -->
<canvas id="userChart" width="400" height="200"></canvas>

<!-- 見出し（タイトル） -->
<h2>オーダー数(店舗毎)の推移</h2>
Order
<!-- プルダウンメニューを見出しの下に配置 -->
<div class="form-group">
    <label for="viewSelector">表示切替：</label>
    <select id="viewSelector" class="form-control">
        <option value="monthly">月ごとのオーダー数</option>
        <option value="weekly">週ごとのオーダー数</option>
        <option value="daily">曜日ごとのオーダー数</option>
    </select>
</div>
<canvas id="orderChart"></canvas>

<!-- 見出し（タイトル） -->
<h2>売上金額(店舗毎)の推移</h2>
Sales
<!-- プルダウンメニューを見出しの下に配置 -->
<div class="form-group">
    <label for="viewSelector2">表示切替：</label>
    <select id="viewSelector2" class="form-control">
        <option value="monthly2">月ごとの売上金額</option>
        <option value="weekly2">週ごとの売上金額</option>
        <option value="daily2">曜日ごとの売上金額</option>
    </select>
</div>
<canvas id="sallesChart"></canvas>

<!-- 見出し（タイトル） -->
<h2>メール(店舗毎)の推移</h2>
Mails
<!-- プルダウンメニューを見出しの下に配置 -->
<div class="form-group">
    <label for="viewSelector3">表示切替：</label>
    <select id="viewSelector3" class="form-control">
        <option value="monthly3">月ごとのメール</option>
        <option value="weekly3">週ごとのメール</option>
        <option value="daily3">曜日ごとのメール</option>
    </select>
</div>
<canvas id="mailsChart"></canvas>

<!-- 見出し（タイトル） -->
<h2>お問合せ(店舗毎)の推移</h2>
Inquiries
<!-- プルダウンメニューを見出しの下に配置 -->
<div class="form-group">
    <label for="viewSelector4">表示切替：</label>
    <select id="viewSelector4" class="form-control">
        <option value="monthly4">月ごとのお問合せ数</option>
        <option value="weekly4">週ごとのお問合せ数</option>
        <option value="daily4">曜日ごとのお問合せ数</option>
    </select>
</div>
<canvas id="inquiriesChart"></canvas>

<!-- 見出し（タイトル） -->
<h2>クーポン(店舗毎)の推移</h2>
Coupon
<!-- プルダウンメニューを見出しの下に配置 -->
<div class="form-group">
    <label for="viewSelector5">表示切替：</label>
    <select id="viewSelector5" class="form-control">
        <option value="monthly5">月ごとのクーポン数</option>
        <option value="weekly5">週ごとのクーポン数</option>
        <option value="daily5">曜日ごとのクーポン数</option>
    </select>
</div>
<canvas id="shop_couponsChart"></canvas>

<!-- 見出し（タイトル） -->
<h2>キャンペーン(店舗毎)</h2>
Campaign
<div class="container">
    <h2>キャンペーンの期間と件数</h2>

    <!-- キャンペーンのグラフを表示 -->
    <canvas id="campaignChart" width="800" height="400"></canvas>
</div>

<h2>商品価格推移</h2>	
    <!-- 商品ごとの価格履歴チャートを表示 -->
    @foreach($productData as $data)
        <div style="margin-bottom: 40px;">
            <h2>{{ $data['name'] }}の価格推移</h2>
            <canvas id="priceChart_{{ $loop->index }}" width="400" height="200"></canvas>
        </div>

        <script>
            // PHPからデータを渡す
            var dates = @json($data['dates']);  // 価格変更日
            var prices = @json($data['prices']);  // 価格

            // 商品ごとのチャートを設定
            var ctx = document.getElementById('priceChart_{{ $loop->index }}').getContext('2d');
            var priceChart = new Chart(ctx, {
                type: 'line',  // ラインチャートを作成
                data: {
                    labels: dates.map(function(date) {
                        return new Date(date).toLocaleDateString(); // 日付を日本語の年月日形式に変換
                    }),
                    datasets: [{
                        label: '{{ $data['name'] }} 価格', // 商品名をラベルとして使用
                        data: prices,
                        borderColor: 'rgba(75, 192, 192, 1)', // 線の色
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // 塗りつぶしの色
                        borderWidth: 1,
                        fill: true  // 線の下を塗りつぶす
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '日付',
                                color: 'white'
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
                                text: '価格 (円)',
                                color: 'white'
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
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    // ツールチップに表示される値のフォーマットを調整
                                    return '価格: ' + tooltipItem.raw.toLocaleString() + ' 円';
                                }
                            }
                        },
                        legend: {
			                labels: {
			                    color: 'white' // 凡例（label）の文字色を白に設定
			                }
			            }
                    }
                }
            });
        </script>
    @endforeach
    



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
	 	var monthlySubOrderCounts = @json($monthlySubOrderCounts);
	 	var weeklySubOrderCounts = @json($weeklySubOrderCounts);
	    var dailySubOrderCounts = @json($dailySubOrderCounts);

	    // 月ごとのデータ
	    var monthlyLabels = monthlySubOrderCounts.map(function(item) {
	        return item.month;
	    });
	    var monthlyData = monthlySubOrderCounts.map(function(item) {
	        return item.count;
	    });

	    // 週ごとのデータ
	    var weeklyLabels = weeklySubOrderCounts.map(function(item) {
	        return 'Week ' + item.week;
	    });
	    var weeklyData = weeklySubOrderCounts.map(function(item) {
	        return item.count;
	    });

	    // 曜日ごとのデータ
	    var dailyLabels = ["日", "月", "火", "水", "木", "金", "土"];
	    var dailyData = dailySubOrderCounts.map(function(item) {
	        return item.count;
	    });

	    // 初期グラフの設定（月ごとのデータ）
	    var chartData = {
	        labels: monthlyLabels,
	        datasets: [{
	            label: 'オーダー数の推移（月ごと)',
	            data: monthlyData,
	            borderColor: 'rgb(255, 99, 132)',
	            fill: false,
	        }]
	    };

        var ctx2 = document.getElementById('orderChart').getContext('2d');
        var orderChart = new Chart(ctx2, {
            type: 'line',
        	data: chartData,
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

	    // プルダウンメニューが変更されたときにグラフを更新
	    document.getElementById('viewSelector').addEventListener('change', function() {
	        var selectedView = this.value;

	        if (selectedView === 'monthly') {
	            orderChart.data.labels = monthlyLabels;
	            orderChart.data.datasets[0].label = 'オーダー数の推移（月ごと）';
	            orderChart.data.datasets[0].data = monthlyData;
	        } else if (selectedView === 'weekly') {
	            orderChart.data.labels = weeklyLabels;
	            orderChart.data.datasets[0].label = 'オーダー数の推移（週ごと）';
	            orderChart.data.datasets[0].data = weeklyData;
	        } else {
	            orderChart.data.labels = dailyLabels;
	            orderChart.data.datasets[0].label = 'オーダー数の推移（曜日ごと）';
	            orderChart.data.datasets[0].data = dailyData;
	        }

	        // グラフを更新
	        orderChart.update();
	    });


	    // 売上金額の推移グラフ
	 	var monthlySubOrderSalles = @json($monthlySubOrderSalles);
	 	var weeklySubOrderSalles = @json($weeklySubOrderSalles);
	    var dailySubOrderSalles = @json($dailySubOrderSalles);

	    // 月ごとのデータ
	    var monthlyLabels2 = monthlySubOrderSalles.map(function(item) {
	        return item.month;
	    });
	    var monthlyData2 = monthlySubOrderSalles.map(function(item) {
	        return item.total_sales;
	    });

	    // 週ごとのデータ
	    var weeklyLabels2 = weeklySubOrderSalles.map(function(item) {
	        return 'Week ' + item.week;
	    });
	    var weeklyData2 = weeklySubOrderSalles.map(function(item) {
	        return item.total_sales;
	    });

	    // 曜日ごとのデータ
	    var dailyLabels2 = ["日", "月", "火", "水", "木", "金", "土"];
	    var dailyData2 = dailySubOrderSalles.map(function(item) {
	        return item.total_sales;
	    });

	    // 初期グラフの設定（月ごとのデータ）
	    var chartData2 = {
	        labels: monthlyLabels2,
	        datasets: [{
	            label: '売上数の推移（月ごと)',
	            data: monthlyData2,
	            borderColor: 'rgb(255, 99, 132)',
	            fill: false,
	        }]
	    };

        var ctx3 = document.getElementById('sallesChart').getContext('2d');
        var sallesChart = new Chart(ctx3, {
            type: 'line',
        	data: chartData2,
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

	    // プルダウンメニューが変更されたときにグラフを更新
	    document.getElementById('viewSelector2').addEventListener('change', function() {
	        var selectedView2 = this.value;

	        if (selectedView2 === 'monthly2') {
	        	// console.log(monthlyLabels2);
	            sallesChart.data.labels = monthlyLabels2;
	            sallesChart.data.datasets[0].label = '売上金額の推移（月ごと）';
	            sallesChart.data.datasets[0].data = monthlyData2;
	        } else if (selectedView2 === 'weekly2') {
	            sallesChart.data.labels = weeklyLabels2;
	            sallesChart.data.datasets[0].label = '売上金額の推移（週ごと）';
	            sallesChart.data.datasets[0].data = weeklyData2;
	        } else {
	            sallesChart.data.labels = dailyLabels2;
	            sallesChart.data.datasets[0].label = '売上金額の推移（曜日ごと）';
	            sallesChart.data.datasets[0].data = dailyData2;
	        }

	        // グラフを更新
	        sallesChart.update();
	    });




        // メール数の推移グラフ
	 	var monthlyMailsCounts = @json($monthlyMailsCounts);
	 	var weeklyMailsCounts = @json($weeklyMailsCounts);
	    var dailyMailsCounts = @json($dailyMailsCounts);

	    // 月ごとのデータ
	    var monthlyLabels3 = monthlyMailsCounts.map(function(item) {
	        return item.month;
	    });
	    var monthlyData3 = monthlyMailsCounts.map(function(item) {
	        return item.count;
	    });

	    // 週ごとのデータ
	    var weeklyLabels3 = weeklyMailsCounts.map(function(item) {
	        return 'Week ' + item.week;
	    });
	    var weeklyData3 = weeklyMailsCounts.map(function(item) {
	        return item.count;
	    });

	    // 曜日ごとのデータ
	    var dailyLabels3 = ["日", "月", "火", "水", "木", "金", "土"];
	    var dailyData3 = dailyMailsCounts.map(function(item) {
	        return item.count;
	    });

	    // 初期グラフの設定（月ごとのデータ）
	    var chartData3 = {
	        labels: monthlyLabels3,
	        datasets: [{
	            label: 'メール数の推移（月ごと)',
	            data: monthlyData3,
	            borderColor: 'rgb(255, 99, 132)',
	            fill: false,
	        }]
	    };

        var ctx4 = document.getElementById('mailsChart').getContext('2d');
        var mailsChart = new Chart(ctx4, {
            type: 'line',
        	data: chartData3,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'ひと月'
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
                            text: 'メール数'
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

	    // プルダウンメニューが変更されたときにグラフを更新
	    document.getElementById('viewSelector3').addEventListener('change', function() {
	        var selectedView3 = this.value;

	        if (selectedView3 === 'monthly3') {
	            mailsChart.data.labels = monthlyLabels3;
	            mailsChart.data.datasets[0].label = 'メール数の推移（月ごと）';
	            mailsChart.data.datasets[0].data = monthlyData3;
	        } else if (selectedView3 === 'weekly3') {
	            mailsChart.data.labels = weeklyLabels3;
	            mailsChart.data.datasets[0].label = 'メール数の推移（週ごと）';
	            mailsChart.data.datasets[0].data = weeklyData3;
	        } else {
	            mailsChart.data.labels = dailyLabels3;
	            mailsChart.data.datasets[0].label = 'メール数の推移（曜日ごと）';
	            mailsChart.data.datasets[0].data = dailyData3;
	        }

	        // グラフを更新
	        mailsChart.update();
	    });


	    // お問合せ数の推移グラフ
	 	var monthlyInquiriesCounts = @json($monthlyInquiriesCounts);
	 	var weeklyInquiriesCounts = @json($weeklyInquiriesCounts);
	    var dailyInquiriesCounts = @json($dailyInquiriesCounts);

	    // 月ごとのデータ
	    var monthlyLabels4 = monthlyInquiriesCounts.map(function(item) {
	        return item.month;
	    });
	    var monthlyData4 = monthlyInquiriesCounts.map(function(item) {
	        return item.count;
	    });

	    // 週ごとのデータ
	    var weeklyLabels4 = weeklyInquiriesCounts.map(function(item) {
	        return 'Week ' + item.week;
	    });
	    var weeklyData4 = weeklyInquiriesCounts.map(function(item) {
	        return item.count;
	    });

	    // 曜日ごとのデータ
	    var dailyLabels4 = ["日", "月", "火", "水", "木", "金", "土"];
	    var dailyData4 = dailyInquiriesCounts.map(function(item) {
	        return item.count;
	    });

	    // 初期グラフの設定（月ごとのデータ）
	    var chartData4 = {
	        labels: monthlyLabels4,
	        datasets: [{
	            label: 'お問合せ数の推移（月ごと)',
	            data: monthlyData4,
	            borderColor: 'rgb(255, 99, 132)',
	            fill: false,
	        }]
	    };

        var ctx5 = document.getElementById('inquiriesChart').getContext('2d');
        var inquiriesChart = new Chart(ctx5, {
            type: 'line',
        	data: chartData4,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'ひと月'
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
                            text: 'お問合せ数'
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

	    // プルダウンメニューが変更されたときにグラフを更新
	    document.getElementById('viewSelector4').addEventListener('change', function() {
	        var selectedView4 = this.value;

	        if (selectedView4 === 'monthly4') {
	            inquiriesChart.data.labels = monthlyLabels4;
	            inquiriesChart.data.datasets[0].label = 'お問合せ数の推移（月ごと）';
	            inquiriesChart.data.datasets[0].data = monthlyData4;
	        } else if (selectedView4 === 'weekly4') {
	            inquiriesChart.data.labels = weeklyLabels4;
	            inquiriesChart.data.datasets[0].label = 'お問合せ数の推移（週ごと）';
	            inquiriesChart.data.datasets[0].data = weeklyData4;
	        } else {
	            inquiriesChart.data.labels = dailyLabels4;
	            inquiriesChart.data.datasets[0].label = 'お問合せ数の推移（曜日ごと）';
	            inquiriesChart.data.datasets[0].data = dailyData4;
	        }

	        // グラフを更新
	        inquiriesChart.update();
	    });


	    // クーポン数の推移グラフ
	 	var monthlyShopCouponCounts = @json($monthlyShopCouponCounts);
	 	var weeklyShopCouponCounts = @json($weeklyShopCouponCounts);
	    var dailyShopCouponCounts = @json($dailyShopCouponCounts);

	    // 月ごとのデータ
	    var monthlyLabels5 = monthlyShopCouponCounts.map(function(item) {
	        return item.month;
	    });
	    var monthlyData5 = monthlyShopCouponCounts.map(function(item) {
	        return item.count;
	    });

	    // 週ごとのデータ
	    var weeklyLabels5 = weeklyShopCouponCounts.map(function(item) {
	        return 'Week ' + item.week;
	    });
	    var weeklyData5 = weeklyShopCouponCounts.map(function(item) {
	        return item.count;
	    });

	    // 曜日ごとのデータ
	    var dailyLabels5 = ["日", "月", "火", "水", "木", "金", "土"];
	    var dailyData5 = dailyShopCouponCounts.map(function(item) {
	        return item.count;
	    });

	    // 初期グラフの設定（月ごとのデータ）
	    var chartData5 = {
	        labels: monthlyLabels5,
	        datasets: [{
	            label: 'クーポン数の推移（月ごと)',
	            data: monthlyData5,
	            borderColor: 'rgb(255, 99, 132)',
	            fill: false,
	        }]
	    };

        var ctx6 = document.getElementById('shop_couponsChart').getContext('2d');
        var shop_couponsChart = new Chart(ctx6, {
            type: 'line',
        	data: chartData5,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'ひと月',
                            color: 'white'
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
                            text: 'クーポン数'
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

	    // プルダウンメニューが変更されたときにグラフを更新
	    document.getElementById('viewSelector5').addEventListener('change', function() {
	        var selectedView5 = this.value;

	        if (selectedView5 === 'monthly5') {
	            shop_couponsChart.data.labels = monthlyLabels5;
	            shop_couponsChart.data.datasets[0].label = 'クーポン数の推移（月ごと）';
	            shop_couponsChart.data.datasets[0].data = monthlyData5;
	        } else if (selectedView5 === 'weekly5') {
	            shop_couponsChart.data.labels = weeklyLabels5;
	            shop_couponsChart.data.datasets[0].label = 'クーポン数の推移（週ごと）';
	            shop_couponsChart.data.datasets[0].data = weeklyData5;
	        } else {
	            shop_couponsChart.data.labels = dailyLabels5;
	            shop_couponsChart.data.datasets[0].label = 'クーポン数の推移（曜日ごと）';
	            shop_couponsChart.data.datasets[0].data = dailyData5;
	        }

	        // グラフを更新
	        shop_couponsChart.update();
	    });

	    // キャンペーン

        var ctx7 = document.getElementById('campaignChart').getContext('2d');

        // PHPから渡されたキャンペーンデータ
        var campaigns = @json($campaigns);

        // 開始日と終了日をDateオブジェクトに変換し、期間の長さを計算
        var campaignNames = campaigns.map(campaign => campaign.name);
        var startDates = campaigns.map(campaign => new Date(campaign.start_date));
        var endDates = campaigns.map(campaign => new Date(campaign.end_date));
        var discountRates = campaigns.map(campaign => campaign.discount_rate);

        // 期間の長さ（日数）
        var durations = startDates.map((start, index) => {
            var end = endDates[index];
            return (end - start) / (1000 * 60 * 60 * 24); // ミリ秒から日数に変換
        });

        // グラフのデータ
        var campaignChart = new Chart(ctx7, {
            type: 'bar', // 横向き棒グラフ
            data: {
                labels: campaignNames,
                datasets: [{
                    label: 'キャンペーン期間',
                    data: durations,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)', // バーを青色に設定
                    borderColor: 'rgba(0, 123, 255, 1)', // バーの枠線を青色に設定
                    borderWidth: 2, // 枠線の太さ
                    barThickness: 20,
                }]
            },
            options: {
                indexAxis: 'y', // 横向き
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '日数',
                            color: 'white' // X軸のタイトルを白に設定
                        },
                        ticks: {
                            color: 'white', // X軸の目盛りを白に設定
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.2)', // X軸の目盛り線を薄い白に設定
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'キャンペーン名',
                            color: 'white' // Y軸のタイトルを白に設定
                        },
                        ticks: {
                            color: 'white', // Y軸の目盛りを白に設定
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.2)', // Y軸の目盛り線を薄い白に設定
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            // ツールチップをカスタマイズ
                            title: function(tooltipItem) {
                                var index = tooltipItem[0].dataIndex;
                                var campaign = campaigns[index];
                                return campaign.name; // キャンペーン名
                            },
                            label: function(tooltipItem) {
                                var index = tooltipItem.dataIndex;
                                var campaign = campaigns[index];
                                var start = startDates[index].toISOString().split('T')[0]; // 開始日 (YYYY-MM-DD)
                                var end = endDates[index].toISOString().split('T')[0]; // 終了日 (YYYY-MM-DD)
                                var duration = durations[index]; // 期間（日数）

                                return '割引率: ' + campaign.discount_rate + '%\n開始日: ' + start + '\n終了日: ' + end + '\n期間: ' + duration + '日';
                            }
                        },
                        titleColor: 'white', // ツールチップのタイトル（キャンペーン名）を白に
                        bodyColor: 'white', // ツールチップの内容（割引率、日付）を白に
                        backgroundColor: 'rgba(0, 0, 0, 0.7)', // ツールチップの背景色を暗く
                        borderColor: '#333', // ツールチップの枠線色
                        borderWidth: 1
                    },
                    legend: {
                        labels: {
                            color: 'white', // 凡例ラベルの文字色を白に設定
                        }
                    }
                },
                elements: {
                    bar: {
                        borderRadius: 5 // 棒の角を丸くする
                    }
                }
            }
        });
    

</script>
@endsection