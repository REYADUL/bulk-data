<?php
function filter($str)
{
    $new_str = trim($str);
    $new_str = str_replace(array("&amp;", "&#39;", "&nbsp;"), array("and", "'", " "), $new_str);
    return $new_str;
}

function get_context()
{
    $options = array(
        "http" => array(
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.81 Safari/537.36',
            'method' => 'GET',
        ),
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            'allow_self_signed' => true,
            'curl_verify_ssl_peer' => false,
            'curl_verify_ssl_host' => false,
        ),
    );
    $context = stream_context_create($options);
    return $context;
}

function get_product($count, $url)
{
    $context = get_context();
    $page = file_get_html($url, false, $context);

    $title = "";
    $description = "";
    $Rprice = "";
    $Sprice = "";
    $img = "";
    $purl = $url;
    $category = "specialist tools";
    if (!empty($page)) {
        if ($page->find('meta[property=og:title]', 0) != NULL) {
            $t = $page->find('meta[property=og:title]', 0);
            $title = ($t->content);
        }
        if ($page->find('meta[name=description]', 0) != NULL) {
            $d = $page->find('meta[name=description]', 0);
            $description = filter($d->content);
        }
        if ($page->find('meta[property=og:price:amount]', 0) != NULL) {
            $p = $page->find('meta[property=og:price:amount]', 0);
            $Rprice = filter($p->content);
        }
        if ($page->find('span#productPrice-product-template.h1 span small s', 0) != NULL) {
            $p = $page->find('span#productPrice-product-template.h1 span small s', 0)->plaintext;
            $Sprice = filter($p);
        }
        if ($page->find('meta[property=og:image:secure_url]', 0) != NULL) {
            $m = $page->find('meta[property=og:image:secure_url]', 0);
            $img = $m->content;
        }
        echo $count . " >>> " . $title . " >>> " . $Rprice . "\n";
        $array = array($count, $title, $description, $category, $Rprice, $Sprice, $url, $img);
        return $array;
    } else
        return NULL;

}

function crawl_page($fp, $head, $domain, $tail)
{
    $count = 0;
    $page = 0;
    $url = $head . $domain . $tail;
    $context = get_context();
    while (++$page) {
        $link = $url . $page;
        echo $link. "\n";
        $html = @file_get_html($link, false, $context);
        // $links = $html->find('div.card__information h3.card__heading  a');

        if ($html) {
            $allLinks = $html->find('div.card__information h3.card__heading  a');

            $links = array_unique(array_map(function($link) {
                return $link->href;
            }, $allLinks));
            
        } else {
            echo "Failed to fetch HTML content from the URL."."\n";
        }
        if (count($links) < 1)
            break;
        echo "PAGE: " . $page . "\n";
        foreach ($links as $l) {
            $product_url = $head . $domain . $l;
            // echo $product_url."\n"; continue;
            $count++;
            $array = get_product($count, $product_url);

            if ($array !== NULL)
                fputcsv($fp, $array);
        }
    }
}