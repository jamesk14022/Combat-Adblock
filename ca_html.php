<?php
//get deafult html and css of inline notification
function ca_default_inline_banner(){
	return <<<EOT
	<head>
	<style>
		div.ca-warn{
		border: 1px solid grey;
		border-radius: 5px;
		padding: 10px;
		}
	</style>
	<body>
	<div class="ca-warn">
		<h2>Please disable your adblocking software to view this premium content.</h2>
	</div>
	</body>
EOT;
}

//get default text for alert box 
function ca_default_alert_text(){
	return 'Please disable your adblocker for premium content!';
}

//get default redirect url for alert boxes
function ca_default_redirect_url(){
	return get_site_url();
}


//load admin dependancy html
function ca_admin_dependancy($ajaxurl){
	return '
	<head>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js"></script>

	<script>
		function deleteStatistics(){
			var c = confirm("Are you sure you wish to delete all user data collected by adblocker?");
		
				var data = {"action": "ca_ajax_adblock_clear"};
				jQuery.post("' . $ajaxurl . '", data, function(response){});
				location.reload();
			
		}
	</script>
	</head>';
}

//load js required for inline adblocking banner
function ca_inline_banner_js($content, $ajaxurl){
	echo "
	<div class=\"ca-cont\">
		$content
	</div>
	";
	return "
	<script>
	// Function called if AdBlock is not detected
	function adBlockNotDetected() {
		con_div = document.getElementsByClassName('ca-cont');
		warn_div = document.getElementsByClassName('ca-warn');
		for(i = 0; i < con_div.length; i++){
			warn_div[i].style.display = 'none';
			con_div[i].style.display = 'block';
		}
	}

	// Function called if AdBlock is detected
	function adBlockDetected() {
		warn_div = document.getElementsByClassName('ca-warn');
		con_div = document.getElementsByClassName('ca-cont');
		for(i = 0; i < warn_div.length; i++){
			warn_div[i].style.display = 'block';
			con_div[i].style.display = 'none';
		}
		//ajax request to log adblcoking attempted - ip and timestamp 
		//are collected server side
		var data = {'action': 'ca_ajax_adblock_log'};
		jQuery.post('$ajaxurl', data, function(response){});
	}

	// Recommended audit because AdBlock lock the file 'fuckadblock.js' 
	// If the file is not called, the variable does not exist 'fuckAdBlock'
	// This means that AdBlock is present
	if(typeof fuckAdBlock === 'undefined') {
		adBlockDetected();
	} else {
		adBlockNotDetected();
	}

	</script>
";
}

//load js required for alert based adblocking
function ca_alert_js($content, $alert_text, $ajaxurl, $redirect, $redirect_url){
	echo "<div style=\"display: none;\" class=\"ca-cont\">
	$content
	</div>
	<script>
	// Function called if AdBlock is not detected
	function adBlockNotDetected() {
		con_div = document.getElementsByClassName('ca-cont');
		warn_div = document.getElementsByClassName('ca-warn');
		for(i = 0; i < con_div.length + 1; i++){
			con_div[i].style.display = 'block';
			warn_div[i].style.display = 'none';
		}
	}

	// Function called if AdBlock is detected
	// we dont need to hide the content div here as it is hidden by default
	function adBlockDetected() {
		alert('$alert_text');
		//ajax request to log adblcoking attempted - ip and timestamp 
		//are collected server side
		var data = {'action': 'ca_ajax_adblock_log'};
		jQuery.post('$ajaxurl', data, function(response){});
	}

	// Recommended audit because AdBlock lock the file 'fuckadblock.js' 
	// If the file is not called, the variable does not exist 'fuckAdBlock'
	// This means that AdBlock is present
	if(typeof fuckAdBlock === 'undefined') {
		adBlockDetected();
	} else {
		adBlockNotDetected();
	}

	// Change the options
	fuckAdBlock.setOption('checkOnLoad', false);
	// and|or
	fuckAdBlock.setOption({
		debug: false,
		checkOnLoad: true,
		resetOnEnd: true,
		loopMaxNumber: 50

	});

	</script>";

	if($redirect == true){
		return '
			<script>
				window.location.replace("' . $redirect_url . '");
			</script>
		';
	}else{
		return;
	}
}

//load html for admin body
function ca_load_admin_body(){
	?>

	<div class="wrap">
	<h1 class="ca_title"><?=  esc_html(get_admin_page_title()); ?></h1>
	<div id="exTab2" class="container">	
		<ul class="nav nav-tabs">
			<li class="active"><a  href="#1" data-toggle="tab">Overview</a></li>
			<li><a href="#2" data-toggle="tab">Statistics</a></li>
		</ul>

		<div class="tab-content ">
		<div class="tab-pane active" id="1">
			<form action="options.php" method="post">
			<?php
				settings_fields('ca_options');

				$warning_type_default = array('type' => 'radio-inline');
				$alert_text_default = array('text' => ca_default_alert_text());
				$banner_default  = array('text' => ca_default_inline_banner());

				$option1 = wp_parse_args(get_option('ca_alert_type'), $warning_type_default);
				$option2 = wp_parse_args(get_option('ca_alert_text'), $alert_text_default);
				$option4 = get_option('ca_alert_redirect');
				$option5 = get_option('ca_alert_redirect_url');
				$option6 = get_option('ca_global_lock');
				$option3 = wp_parse_args(get_option('ca_inline_banner_code'), $banner_default);					
			?>
			<table class="form-table">
				<tr valign="top"><th scope="row">How would you like Adblock content warnings to appear?</th>
	                <td><p>As an inline banner</p></td><td><input type="radio" name="ca_alert_type[type]" value="radio-inline" <?php checked('radio-inline', $option1['type']); ?>/></td>
	                <td><p>As an alert box</p></td><td><input type="radio" name="ca_alert_type[type]" value="radio-alert" <?php checked('radio-alert', $option1['type']); ?>/></td>
				</tr>
				<tr valign="top"><th scope="row">Enter alert text here if you're using an alert box</th>
	                <td><input type="text" name="ca_alert_text[text]" value="<?php echo $option2['text']; ?>"/></td>
				</tr>
				<tr valign="top"><th scope="row">Redirect after alert (default url is homepage)</th>
	                <td><input type="checkbox" name="ca_alert_redirect[type]" value="checkbox-redirect" <?php checked(isset($option4['type'])); ?>/></td>
				</tr>
				<tr valign="top"><th scope="row">Custom redirect URL</th>
	                <td><input type="text" name="ca_alert_redirect_url[text]" value="<?php echo $option5['text']; ?>"/></td>
				</tr>
				<tr valign="top"><th scope="row">Toggle Global Lock on all content</th>
	                <td><input type="checkbox" name="ca_global_lock[type]" value="checkbox-global-lock" <?php checked(isset($option6['type'])); ?>/></td>
				</tr>
				<tr valign="top"><th scope="row">Inline Banner HTML/CSS</th>
	                <td><textarea name="ca_inline_banner_code[text]"><?php echo esc_html($option3['text']); ?></textarea></td>
				</tr>
	        </table>
	            <?php submit_button('Save Settings'); ?>
	        </form>
		</div>
		<div class="tab-pane" id="2">
			<button type="button" class="btn btn-default btn-clear-data" onClick="deleteStatistics();">Clear All Data</button>

			<canvas id="myChart" width="250" height="150"></canvas>
			<script>


	    	var data = {
	          datasets: [{
	              label: 'Adblockers Stopped per Day',
	              data: [

	              <?php 
					$i = 0;
	              	foreach(ca_get_ip_rows() as $row){
	              		echo '{x: moment(\'' . $row['datetime'] . '\'), y: ' . $row['adblocker_count'] . '}';
	              		$i++;
	              		if($i < count(ca_get_ip_rows())){
	              			echo ',';
	              		}
	              	}
					?>
					],
	              backgroundColor: '#A4458C',
	            },

	          ]
	        };

	        var ctx = document.getElementById("myChart").getContext("2d");
	        var myLineChart = new Chart(ctx, {
	          type: 'line',
	          data: data,
	          options: {
	            fill: false,
	            responsive: true,

	            scales: {
	              xAxes: [{
	                type: 'time',
	                time: {unit: 'day'},
	                display: true,
	                scaleLabel: {
	                  display: true,
	                  labelString: "Date",
	                }
	              }],

	              yAxes: [{
	                display: true,
	                scaleLabel: {
	                  display: true,
	                  labelString: "Adblockers Stopped",
	                }
	              }]
	            }
	          },
	        });
			</script>
		</div>
		</div>
	</div>
</div>
	<?php
}

?>