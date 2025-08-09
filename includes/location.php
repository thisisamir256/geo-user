<?php 

// Exit if accessed directly
defined('ABSPATH') || exit;

require_once 'functions.php';

//Get all users
$users = get_users();
$customer_statictics_url = menu_page_url('customer-statistics',false);

?>
<div class="wrapper" style="height:100%;">
	<?php
foreach ($users as $user) {
	$long = get_user_meta($user->ID, 'long', true);
    $lat = get_user_meta($user->ID, 'lat', true);
	if (is_null($long) || is_null($lat)) {
		continue;
	}
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

?>
	<h1><?php _e('Users on the map')?></h1>
<div id="map" style="width: 100%; height:90vh;">
</div>    
</div>
<script>
	let users = <?php echo json_encode($users_meta);?>;		
	let customerStatisticsUrl = '<?php echo $customer_statictics_url; ?>';	
	let editUser = '<?php _e('Edit User') ?>';

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
		let userLink = `<a href="${user.user_edit_link}"target="_blank">${editUser}</a>`;
		var marker = L.marker([user.lat,user.long],{icon: greyIcon})
					.bindPopup(userLink)
					.addTo(map);		
	});
	map.setView([38.0792, 46.2887], 2.3)
}

</script>