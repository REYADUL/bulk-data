<?php
function shopCrawler($link,$fp,$head, $domain, $tail,$count){
    $count = 1;
    $p=1;
    while(1)
        {
            
            $link = "https://infu.com.au/shop/page/".$p ;

            echo $link."\nEntering Page-".$p.":\n";
            $html = file_get_html($link, false, get_context());
            $links = $html->find('div.product-buttons-container a.show_details_button');
            foreach($links as $l)
            {
                $array = get_product(++$count, $l->href);
                if($array == NULL) {
                    echo $count.">> shop >>> Product not Loading...\n";
                    continue;
                } else if($array == "OOS") {
                    echo $count.">>shop >>> Out of Stock >>> Skipping...\n";
                    continue;
                }
                $count++;
                echo $count.">>> shop  >>> ".$array['title']." >>> ".$array['RegularPrice']." - ".$array['SalePrice']."\n";
                fputcsv($fp, $array);
            }
            echo 'going to second'."\n";
            if($html->find('nav.woocommerce-pagination a.next'))
                $p++;
            else
                break;
        }
        return $count;
}

?>

