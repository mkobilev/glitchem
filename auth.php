<?php

require_once('lib/vkapi/VK.php');
require_once('lib/image.php');

session_start();

// app settings 
$vkConf = Array(
    'appID'       => YOUR_STANDALONE_APP_ID,
    'apiSecret'   => YOUR_STANDALONE_APP_KEY,
    'callbackUrl' => YOUR_SERVER_URL/auth.php',
 );
 
// vk auth
if(isset($_REQUEST['code'])) {
    $vk = new VK\VK($vkConf['appID'], $vkConf['apiSecret']);
    $accessToken = $vk -> getAccessToken($_REQUEST['code'], $vkConf['callbackUrl']);
    $_SESSION['accessToken'] = $accessToken['access_token'];
 } else {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /?act=main");
 }
 
$userId = $accessToken['user_id'];
$userDir = "users/{$accessToken['user_id']}";

if(file_exists($userDir)) {
    gotoUserPage($userId);
 } else {
    createUserContent($userId, $vk);
    gotoUserPage($userId);
    
 }

 function gotoUserPage($userId){
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /?act=glitch&uid={$userId}");
}

function createUserContent($userId, $vk) {
    global $userDir;
    try {
        @mkdir("{$userDir}/img/", 0755, true);
    } catch (Exception $e) {
        echo 'mkdir error: ',  $e->getMessage(), "\n";
        }
    
    // vkapi request fields
    $field = array(
        'uid' => $userId,
        'count' => 25,
        'order' => 'hints',
        'fields' => 'photo_200',
        'name_case' => 'nom' );
        
    $friendsList = $vk -> api('friends.get',  $field );

    for ($i =0; $i<25; $i++) {
        $ind = $i + 1;
        if ($friendsList['response'][$i]['photo_200']){
        
            $imgUrl = $friendsList['response'][$i]['photo_200'];
        }
        else {
        $imgUrl = '__404.jpg';
        }
            copy($imgUrl, "{$userDir}/img/{$ind}_orig.jpg");
            $origImgPath = "{$userDir}/img/{$ind}_orig.jpg";
            gluk($userDir, $origImgPath, $ind);
            if ($ind <=5) {
                filtersCreate( $userDir, $origImgPath, $ind );
            }
    }

    $filter = array('rbg','bgr','brg','gbr','grb');
    $puzzleFilter = imagecreatetruecolor(1000, 1000);   
    $y = 0;
    
    for ($i =0; $i<5; $i++) {
        $ind = $i + 1;
        $x = 0;
            foreach ($filter as &$value) {
                $img_glitch = imagecreatefromjpeg("{$userDir}/img/{$ind}_{$value}.jpg");
                imagecopy($puzzleFilter, $img_glitch, $x, $y, 0, 0, 200, 200);
                imagedestroy($img_glitch);
                $x+=200;
            }    
        $y+=200;
    }


    $puzzleGlitch = imagecreatetruecolor(1000, 1000);   
    $puzzleOrig = imagecreatetruecolor(1000, 1000);   
    $k=0;
    for ($x =0; $x<801; $x+=200){
        for ($y =0; $y<801; $y+=200){
        
        $k+=1;
        
        while(jpegIsValid("{$userDir}/img/{$k}_gluk.jpg") == 0){
        gluk($userDir, "{$userDir}/img/{$k}_orig.jpg", $k);
        }
        
        $img_glitch = imagecreatefromjpeg("{$userDir}/img/{$k}_gluk.jpg");
        imagecopy($puzzleGlitch, $img_glitch, $y, $x, 0, 0, 200, 200);
        $img_orig = imagecreatefromjpeg("{$userDir}/img/{$k}_orig.jpg");
        imagecopy($puzzleOrig, $img_orig, $y, $x, 0, 0, 200, 200);
       
        imagedestroy($img_glitch);
        imagedestroy($img_orig);
        
        }
    }

    imagejpeg($puzzleFilter, "{$userDir}/img/big_filter.jpg");
    imagedestroy($puzzleFilter);
    imagejpeg($puzzleGlitch, "{$userDir}/img/big_glitch.jpg");
    imagedestroy($puzzleGlitch);
    imagejpeg($puzzleOrig, "{$userDir}/img/big_orig.jpg");
    imagedestroy($puzzleOrig);
    
}

