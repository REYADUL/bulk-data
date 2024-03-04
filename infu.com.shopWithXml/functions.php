<?php
    function get_context() {
        $options  = array(
            "http" => array(
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.81 Safari/537.36',
                'method' => 'GET',
            ), 
            "ssl"=>array(
                "verify_peer"           =>  false,
                "verify_peer_name"      =>  false,
                'allow_self_signed'     =>  true,
                'curl_verify_ssl_peer'  =>  false,
                'curl_verify_ssl_host'  =>  false,
            ),
        );
        $context  = stream_context_create($options);
        return $context;
    }

    function price_filter($price) {
        $new_price = str_replace(array("$", "AUD", " ", ",", "&#36;"),array("", "", "", "", ""),$price);
        $new_price = (float)trim($new_price);
        return $new_price;
    }

    function crawl_page($fp, $count, $head, $domain, $tail) {
        $link = $head.$domain.$tail;
        echo $domain.":\n";
        $xml = file_get_html($link, false, get_context());

        if($xml == NULL || $xml == '') {
            echo "Page Not Found! Continue...\n";
            return $count;
        }
        $links = $xml->find('url loc');

        foreach($links as $l) {
            if(strpos($l->plaintext, "/shop/")){
                // continue;
                $p = shopCrawler($l->plaintext,$fp,$head, $domain, $tail,$count);
                echo $count= $p;
                continue;
            }
            $array = get_product(++$count, $l->plaintext);
            if($array == NULL) {
                echo $count." >>> Product not Loading...\n";
                continue;
            } else if($array == "OOS") {
                echo $count." >>> Out of Stock >>> Skipping...\n";
                continue;
            }
            echo $count." >>> ".$array['title']." >>> ".$array['RegularPrice']." - ".$array['SalePrice']." >>> ".$array['category']."\n";
            fputcsv($fp, $array);
        }
        return $count;
    }

    function get_product($count, $product_url)
    {
        $product_array  = array();
        $title          = "";
        $description    = "";
        $category       = "";
        $RegularPrice   = "";
        $SalePrice      = "";
        $img            = "";
        $purl           = $product_url;

        $page           = @file_get_html($product_url, false, get_context());
        echo $product_url."\n";

        if($page == NULL || $page == '') {
            return NULL;
        } else {
            if($page->find('meta[name=twitter:data2]',0) != NULL) {
                if(strpos($page->plaintext, "Out of stock")){
                    return "OOS";
                }
            }

            if($page->find('meta[property=og:title]',0) != NULL) {
                $t=$page->find('meta[property=og:title]',0);
                $title = $t->content;
            }

            if($page->find('meta[property=og:description]',0) != NULL) {
                $d = $page->find('meta[property=og:description]',0);
                $description = trim($d->content);
            }

            if($page->find('p.price s.rrp_cont',0) != NULL) {
                $p = $page->find('p.price s.rrp_cont',0);
                // echo $p->plaintext."\n";
                $RegularPrice = price_filter($p->plaintext);
                $p = $page->find('p.price span.woocommerce-Price-amount.amount bdi',0);
                // echo $p->plaintext."\n";
                $SalePrice = price_filter($p->plaintext);

            } else {
                $p = $page->find('p.price span.woocommerce-Price-amount.amount bdi',0);
                // echo $p->plaintext."\n";
                $SalePrice = price_filter($p->plaintext);
                
            }

            if($RegularPrice == NULL || $RegularPrice == "" || $RegularPrice <= $SalePrice) {
                $RegularPrice = $SalePrice;
                $SalePrice = "";
            }

            if($page->find('meta[property=og:image]',0) != NULL) {
                $m = $page->find('meta[property=og:image]',0);
                $img = $m->content;
            }

            if($page->find('div.product_meta span.posted_in a') != NULL) {
                $allCategory = $page->find('div.product_meta span.posted_in a');
                // $category = $c->plaintext;
                $allCat = 'Infinity Furniture';
                foreach($allCategory as $cat)
                {
                    $allCat .= " > ".$cat->plaintext;
                }
                $category = $allCat;
            }

            $product_array = array(
                'ID'        => $count, 
                'title'     => $title, 
                'description'=> $description, 
                'category'  => $category, 
                'RegularPrice'=> $RegularPrice, 
                'SalePrice' => $SalePrice, 
                'URL'       => $product_url, 
                'imageURL'  => $img
            );
        }
        return $product_array;
    }
?>