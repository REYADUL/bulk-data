<?php
    header('Content-Type: text/html; charset=UTF-8');
    ini_set("memory_limit", "-1");
    set_time_limit(0);
    ini_set('default_charset', 'utf-8');

    require_once('dom.php');
    require_once('functions.php');
    require_once('shop.php');

    $head = "https://";
    $domain = "infu.com.au";
    $tail = "/product-sitemap1.xml";
    $tail2 = "/product-sitemap2.xml";
    $tail3 = "/product-sitemap3.xml";
    $tail4 = "/product-sitemap4.xml";
    $tail5 = "/product-sitemap5.xml";
    $tail6 = "/product-sitemap6.xml";

    $fp = fopen($domain.'.csv', 'w');
    $array = array('ID', 'Title', 'Description', 'Category', 'RegularPrice', 'SalePrice', 'URL', 'ImageURL');
    fputcsv($fp, $array);

    $count = 0;

    $count = crawl_page($fp, $count, $head, $domain, $tail);
    $count = crawl_page($fp, $count, $head, $domain, $tail2);
    $count = crawl_page($fp, $count, $head, $domain, $tail3);
    $count = crawl_page($fp, $count, $head, $domain, $tail4);
    $count = crawl_page($fp, $count, $head, $domain, $tail5);
    $count = crawl_page($fp, $count, $head, $domain, $tail6);

    fclose($fp);
	echo "<!DONE!>\n";