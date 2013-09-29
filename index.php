<?php

require_once('lib/image.php');

$userId = (uidIsValid($_REQUEST['uid']) && isset($_REQUEST['uid']) ) ? uidToInt($_REQUEST['uid']) : '' ;
include("pages/top.html");


function uidToInt($uid) {
    return( intval($uid) );
}

function uidIsValid($uid) {

    return ( strlen($uid) < 10 ) ? true : false; 
}

function changeAct($page) {

    if ( isset($_REQUEST['uid']) && uidIsValid($_REQUEST['uid']) ){
    
    $userId = uidToInt($_REQUEST['uid']);
    
        if(file_exists("users/{$userId}")) {
            include("pages/{$page}");
        } else {
            include("pages/404.html");
            }
        
    } else {
        include("pages/index.html");
        }
}


if(isset($_REQUEST['act'])) {
    switch ($_REQUEST['act']) {
        case 'main':
            include("pages/index.html");
            break;
        case 'about':
            include("pages/about.html");
            break;  
        case 'orig':
             changeAct("orig.html");
            break;                
        case 'glitch':
            changeAct("glitch.html");
            break;
        case 'filter':
            changeAct("filter.html");
            break;        
        default:
            include("pages/404.html");
            break;
        }
    } else {
        include("pages/index.html");
}
