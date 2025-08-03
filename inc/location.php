<?php 

// Exit if accessed directly
defined('ABSPATH') || exit;

require_once 'functions.php';
$args = [
	'role__in' => [
		'customer',
	]
	];
	$args = [];
$users = get_users($args);
$customer_statictics_url = menu_page_url('customer-statistics',false);

?>
<div class="wrapper" style="height:100%;">
	<?php
foreach ($users as $user) {
	$long = get_user_meta($user->ID, 'long', true);
    $lat = get_user_meta($user->ID, 'lat', true);
	$company = get_user_meta($user->ID, 'billing_company',true);
	if($long && $lat) {
	$users_meta[] = [
		'ID' => $user->ID,
		'long' => $long,
		'lat' => $lat,
		'company' => $company,
		'user_edit_link'=> get_edit_user_link($user->ID),
	];
}
}
if (is_woocommerce_activated()) {
	$sum_order = 0;
	$count_order = 0 ;
	foreach ($users_meta as &$value) {
		$user_sum_order = 0;
		$args = [
			'customer_id' =>	$value["ID"],
			'limit' 	  =>	-1,
		];
		$orders = wc_get_orders($args);
		if ($orders) {
			$count_order ++;
			foreach ($orders as $order) {
				$user_sum_order += $order->get_total();
			}			
		}
			

			$value['total_orders'] =$user_sum_order;
			$sum_order += $user_sum_order;
		
	}
}
$order_first_quartile = $sum_order/4;
$order_second_quartile = $sum_order/2;
$order_third_quartile = $sum_order* 2/3;
$order_fourth_quartile = $sum_order;

?>
	<h1>پراکندگی مشتری‌ها روی نقشه</h1>
	<!-- <table>
	<tr>
		<td style="padding-left:15px;"><img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-grey.png"/><strong>صفر</strong></td>
		<td style="padding-left:15px;"><img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-yellow.png"/><strong>چارک اول</strong></td>
		<td style="padding-left:15px;"><img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png"/><strong>چارک دوم</strong></td>
		<td style="padding-left:15px;"><img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-violet.png"/><strong>چارک سوم</strong></td>
		<td style="padding-left:15px;"><img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png"/><strong>چارک چهارم</strong></td>
	</tr>
</table> -->
<div id="map" style="width: 100%; height:90vh;">
</div>    
</div>
<script>
	let users = <?php echo json_encode($users_meta);?>;	
	let FirstQuartile = <?php echo $order_first_quartile; ?>;
	let secondQuartile = <?php echo $order_second_quartile; ?>;
	let thirdQuartile = <?php echo $order_third_quartile; ?>;
	let fourthQuartile = <?php echo $order_fourth_quartile; ?>;
	let customerStatisticsUrl = '<?php echo $customer_statictics_url; ?>';	

	if (Array.isArray(users)) {
	const map = L.map('map');	
	const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);
	let greyIcon = new L.Icon({
  		iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
  		shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  		iconSize: [25, 41],
  		iconAnchor: [12, 41],
  		popupAnchor: [1, -34],
  		shadowSize: [41, 41]
		}),
		yellowIcon = new L.Icon({
  		iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
  		shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  		iconSize: [25, 41],
  		iconAnchor: [12, 41],
  		popupAnchor: [1, -34],
  		shadowSize: [41, 41]
		}),
		orangeIcon = new L.Icon({
  		iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
  		shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  		iconSize: [25, 41],
  		iconAnchor: [12, 41],
  		popupAnchor: [1, -34],
  		shadowSize: [41, 41]
		}),
		violetIcon = new L.Icon({
  		iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
  		shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  		iconSize: [25, 41],
  		iconAnchor: [12, 41],
  		popupAnchor: [1, -34],
  		shadowSize: [41, 41]
		}),
		redIcon = new L.Icon({
  		iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
  		shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  		iconSize: [25, 41],
  		iconAnchor: [12, 41],
  		popupAnchor: [1, -34],
  		shadowSize: [41, 41]
		});
	users.forEach(user => {		
		let userStatisticsUrl = customerStatisticsUrl + '&user_id=' + user.ID;
		let userLink = `<a href="${userStatisticsUrl}" target="_blank">آمار فروشگاه</a><br>
						<a href="${user.user_edit_link}"target="_blank">اطلاعات فروشگاه</a>`;
		switch (true) {			
			case user.total_orders === 0 :
				var marker = L.marker([user.lat,user.long],{icon: greyIcon})
					.bindPopup(userLink)
					.addTo(map);		
				break;
			case user.total_orders <= FirstQuartile:
				var marker = L.marker([user.lat,user.long],{icon: yellowIcon})
					.bindPopup(userLink)
					.addTo(map);		
				break;
			case user.total_orders > FirstQuartile && user.total_orders <= secondQuartile:
				var marker = L.marker([user.lat,user.long],{icon: orangeIcon})
					.bindPopup(userLink)
					.addTo(map);		
				break;
			case user.total_orders > secondQuartile && user.total_orders <= thirdQuartile:
				var marker = L.marker([user.lat,user.long],{icon: violetIcon})
					.bindPopup(userLink)
					.addTo(map);		
				break;	
			case  user.total_orders >= thirdQuartile:
				var marker = L.marker([user.lat,user.long],{icon: redIcon})
					.bindPopup(userLink)
					.addTo(map);		
				break;	
		}
	});
	map.setView([38.0792, 46.2887], 13)
}

</script>