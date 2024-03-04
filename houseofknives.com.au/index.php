<?php
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        require_once('dom.php');
        require_once('functions.php');
        
        $head = "https://www.";
		$domain = "houseofknives.com.au";
		$tail = "/collections/all?page=";
        $fp = fopen($domain.'.csv', 'w');
        $array = array('ID', 'Title', 'Description', 'Category', 'RegularPrice', 'SalePrice', 'URL', 'ImageURL');
        fputcsv($fp, $array);
        crawl_page($fp, $head, $domain, $tail);
        fclose($fp);
		echo "<!DONE!>\n";