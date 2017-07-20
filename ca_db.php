<?php

	//creates table which stores users details and times of those who used adblocker
	function ca_create_ip_table(){
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name = $wpdb->prefix . 'ca_ips';

		//check if table already exists
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
				ip varchar(45) NOT NULL,
				adblocker int NOT NULL,
				datetime varchar(12) NOT NULL)$charset_collate;";
			
			dbDelta($sql);
		}
	}

	//inserts ip row
	function ca_insert_ip_row($ip){
		global $wpdb;
		$table_name = $wpdb->prefix . 'ca_ips';
		$wpdb->insert($table_name, array('id' => '', 'ip' => $ip, 'datetime' => date('Y-m-d'), 'adblocker' => 1));
	}

	//gets all ip rows and returns as a numerically indexed array of arrays, using column names as keys
	//groups by date and adds all adlblock hits for a single date in a single rowforeach
	function ca_get_ip_rows(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'ca_ips';
		return $wpdb->get_results("SELECT datetime, COUNT(adblocker) as adblocker_count FROM $table_name GROUP BY DATE(datetime)", ARRAY_A);
	}

	//total number of ads blocked
	function ca_get_total_logs(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'ca_ips';
		return count($wpdb->get_results("SELECT * FROM $table_name", ARRAY_A));
	}

	//total number of ads blocked, input represents the number of days worth of blocking you wish to select
	function ca_get_interval_logs($interval_days){
		global $wpdb;
		$table_name = $wpdb->prefix . 'ca_ips';
		return count($wpdb->get_results("SELECT * FROM $table_name WHERE datetime >= NOW() - INTERVAL $interval_days DAY", ARRAY_A));
	}

	// clears the ip table of all entries
	function ca_clear_ip_rows(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'ca_ips';
		$wpdb->query("truncate $table_name");
	}

	//retrives graph data in correct format for chartjs
	function ca_admin_graph_data(){
	$i = 0;
	$str = '';
  	foreach(ca_get_ip_rows() as $row){
  		$str = $str . '{x: moment(\'' . $row['datetime'] . '\'), y: ' . $row['adblocker_count'] . '}';
  		$i++;
  		if($i < count(ca_get_ip_rows())){
  			$str = $str .  ',';
  		}else{
  			return $str;
  		}
  	}
}
?>