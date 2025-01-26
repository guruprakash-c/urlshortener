<?php 
use App as A; 

require_once 'DBConfig.php';

ob_start();
// Handle redirection
if (!empty($_GET['code'])) {
    $shortCode = $_GET['code'];
    $urlObj = new A\DBConfig;
    $response = $urlObj->GetUrl($shortCode);
    if(!empty($response) && (intval($response->statusCode) == 200)){
        $ref = array(
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'ref' => 'devstoc',
            'utm_campaign' => 'UrlShortnr'
        );
        $redirect_url = $response->shortUrl.'?'.http_build_query($ref);
        header("Location: " . $redirect_url,true,301);
        exit();
    }else {
        header("Location: index.html");
    }
}else{
    header("Location: index.html");
}

//<iframe src="http://localhost/urlshortener/htwwni" title="W3Schools Free Online Web Tutorials"></iframe>