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
		div.ca-warn h2{
		padding: 0px;
		margin: 0px;
		}
	</style>
	<body>
		<h2>Please disable your adblocking software to view this premium content.</h2>
	</body>
EOT;
}

//get default html and css of modal notification 
function ca_default_modal(){
	return '
	<style>
	#modal img{
	float: left;
	width: 100px;
	height: 100px;
	margin-right: 40px;
	margin-top: 20px;
	}
	#modal h2{
	color: #b53f3f;
	display: inline;
	}
	#modal h3{
	display: inline-block;
	width: 100%;
	margin: 0px;
	padding: 0px;
	max-width: 700px;
	}
	</style>
	<div id="modal">
	<img src="' . plugin_dir_url(__FILE__) . 'assets/eye.png">
	<h2>Adblocker Detected</h2>
	<h3>We have noticed youre using adbocker, this site uses money from ads to pay our 
	server costs, please consider pausing your ad blocking software and refreshing the 
	page to view our premium content.</h3>
	</div>
';
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

	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js"></script>

	<script>

		//triggered(ha) when button in stats panel is clicked
		function deleteStatistics(){
			var c = confirm("Are you sure you wish to delete all user data collected by adblocker?");
			if(c == true){
				var data = {"action": "ca_ajax_adblock_clear"};
				jQuery.post("' . $ajaxurl . '", data, function(response){});
				location.reload();
			}
		}

		//chart js graph render using data entered with php
		window.onload = function(){
			var data = {
	          datasets: [{
	              label: "Adblockers Stopped per Day",
	              data: [' .  ca_admin_graph_data() . '], backgroundColor: "#A4458C",},
			  ]};

	        var ctx = document.getElementById("myChart").getContext("2d");
	        var myLineChart = new Chart(ctx, {
	          type: "line",
	          data: data,
	          options: {
	            fill: false,
	            responsive: true,

	            scales: {
	              xAxes: [{ type: "time", time: {unit: "day"}, display: true, scaleLabel: {
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
	              }]}}, });
		};
	</script>
	</head>';
}


//load js required for inline adblocking banner
function ca_inline_banner_js($warn_html, $content, $ajaxurl){
	echo "
	<div class=\"ca-warn\">
		$warn_html
	</div>
	<div class=\"ca-cont\">
		$content
	</div>
	<script type=\"text/javascript\">
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

//load js required for inline adblocking banner and modal to show 
function ca_modal_js($warn_html, $content, $ajaxurl, $modal_html){
	echo "
	<div class=\"ca-warn\">
		$warn_html
	</div>
	<div class=\"ca-cont\">
		" . $content . "
	</div>
	<head>
	<style>
	/* The Modal (background) */
	.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
	}

	/* Modal Content/Box */
	.modal-content {
	    background-color: #fefefe;
	    margin: 15% auto; /* 15% from the top and centered */
	    padding: 20px;
	    border: 1px solid #888;
	    width: 70%; /* Could be more or less, depending on screen size */
	}

	/* The Close Button */
	.close {
	    color: #aaa;
	    float: right;
	    font-size: 28px;
	    font-weight: bold;
	}

	.close:hover,
	.close:focus {
	    color: black;
	    text-decoration: none;
	    cursor: pointer;
	}

	</style>
	<body>
	<div class=\"modal\" id=\"ca-modal\">
	<div class=\"modal-content\">
		<span class=\"close\">&times;</span>
		$modal_html
	</div>
	</div>
	<script>
	// Get the modal
	var modal = document.getElementById('ca-modal');

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName(\"close\")[0];


	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
	    modal.style.display = \"none\";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	    if (event.target == modal) {
	        modal.style.display = \"none\";
	    }
	}
	</script>
	</body>
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
		var modal = document.getElementById('ca-modal');
		modal.style.display = 'block';
		//ajax request to log adblcoking attempted - ip and timestamp 
		//are collected server side
		var data = {'action': 'ca_ajax_adblock_log'};
		jQuery.post('$ajaxurl', data, function(response){});
	}

	// Recommended audit because AdBlock lock the file 'fuckadblock.js' 
	// If the file is not called, the variable does not exist 'fuckAdBlock'
	// This means that AdBlock is present
	window.onload = function (){
	if(typeof fuckAdBlock === 'undefined') {
		adBlockDetected();
	} else {
		adBlockNotDetected();
	}
	};

	</script>
";
}

//load js required for alert based adblocking
function ca_alert_js($content, $alert_text, $ajaxurl, $redirect, $redirect_url){
	//double quotes used here instead of heredoc as a large amount of variables need to be interpolated,
	//will clean this with a cleaner solution in future version 
	echo "<div style=\"display: none;\" class=\"ca-cont\">
		$content
	</div>
	<script>
	// Function called if AdBlock is not detected
	function adBlockNotDetected() {
		con_div = document.getElementsByClassName('ca-cont');
		for(i = 0; i < con_div.length; i++){
			con_div[i].style.display = 'block';
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
	<div id="exTab2">	
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
				$modal_default = array('text' => ca_default_modal());

				$option1 = wp_parse_args(get_option('ca_alert_type'), $warning_type_default);
				$option2 = wp_parse_args(get_option('ca_alert_text'), $alert_text_default);
				$option4 = get_option('ca_alert_redirect');
				$option5 = get_option('ca_alert_redirect_url');
				$option6 = get_option('ca_global_lock');

				if(ctype_space(get_option('ca_inline_banner_code')['text']) || get_option('ca_inline_banner_code')['text'] == ''){
					$option3 =  $banner_default;
				}else{
					$option3 = wp_parse_args(get_option('ca_inline_banner_code'), $banner_default);
				}

				if(ctype_space(get_option('ca_modal_code')['text']) || get_option('ca_modal_code')['text'] == ''){
					$option7 = $modal_default;
				}else{
					$option7 = wp_parse_args(get_option('ca_modal_code'), $modal_default);
				}

			?>
			<table class="form-table ca-form-table">
				<tr valign="top"><th scope="row">How would you like Adblock content warnings to appear?</th>
	                <td class="radio-type">
		                <div class="block-control"><input type="radio" name="ca_alert_type[type]" value="radio-inline" <?php checked('radio-inline', $option1['type']); ?>/><p>As an inline banner</p></div>
		                <div class="block-control"><input type="radio" name="ca_alert_type[type]" value="radio-alert" <?php checked('radio-alert', $option1['type']); ?>/><p>As an alert box</p></div>
		                <div class="block-control"><input type="radio" name="ca_alert_type[type]" value="radio-modal" <?php checked('radio-modal', $option1['type']); ?>/><p>As a JQuery Pop Up</p></div>
	                </td>
				</tr>
				<tr valign="top"><th scope="row">Enter alert text here if you're using an alert box</th>
	                <td><textarea class="textarea-alert" type="text" name="ca_alert_text[text]"><?php echo $option2['text']; ?></textarea></td>
				</tr>
				<tr valign="top"><th scope="row">Redirect after alert</th>
	                <td><input type="checkbox" name="ca_alert_redirect[type]" value="checkbox-redirect" <?php checked(isset($option4['type'])); ?>/></td>
				</tr>
				<tr valign="top"><th scope="row">Custom redirect URL </th>
	                <td><input class="txt-url" type="text" name="ca_alert_redirect_url[text]" value="<?php echo $option5['text']; ?>"/><br><p class="label-hint">(default is homepage), include http:// or https://</p></td>
				</tr>
				<tr valign="top"><th scope="row">Toggle Global Lock on all content</th>
	                <td><input class="check-global-lock" type="checkbox" name="ca_global_lock[type]" value="checkbox-global-lock" <?php checked(isset($option6['type'])); ?>/><br><p class="label-hint">If enabled, please ensure no [combat-adblock] short <br> codes are present in the site, they will be redundant.</p></td>
				</tr>
				<tr valign="top"><th scope="row">Inline Banner HTML/CSS</th>
	                <td><textarea class="textarea-code" name="ca_inline_banner_code[text]"><?php echo esc_html($option3['text']); ?></textarea><br><p class="label-hint">HTML/CSS OK</p></td>
				</tr>
				<tr valign="top"><th scope="row">Jquery Pop Up HTML/CSS</th>
	                <td><textarea class="textarea-code" name="ca_modal_code[text]"><?php echo esc_html($option7['text']); ?></textarea><br><p class="label-hint">HTML/CSS OK. `Please wrap your content in a div if you wish to style it, <br>other elements on the page may be styled accidently if you dont.</p></td>
				</tr>
	        </table>
	            <?php submit_button('Save Settings'); ?>
	        </form>
		</div>
		<div class="tab-pane" id="2">
			<button type="button" class="btn btn-default btn-clear-data" onClick="deleteStatistics();">Clear All Data</button>

			<canvas id="myChart"></canvas>

			<hr>

			<div class="container">
			<div class="row">
			<div class="col-md-3">
			<div class="ab-stats">
				<div class="stats-heading">Adblockers Stopped(Total)</div>
				<div class="stats-figure"><?php echo ca_get_total_logs(); ?></div>
			</div>
			</div>
			<div class="col-md-3">
			<div class="ab-stats">
				<div class="stats-heading">Adblockers Stopped(This Week)</div>
				<div class="stats-figure"><?php echo ca_get_interval_logs(7); ?></div>
			</div>
			</div>
			</div>
			</container>
		</div>
		</div>
	</div>
	<p>Created by James Kingsbury. For support, feature requests and freelance work - jkingsbury@me.com</p>
</div>
	<?php
}

?>