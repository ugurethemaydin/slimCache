<?php
/**
 * Created by Metromedya
 * http://metromedya.com
 * User: ugurethemaydin
 * Date: 24/02/2017
 * Time: 00:03
 * UEAHelper.php in UEA
 */
///HELPER Methods
function isValidMd5 ($md5=''){
    return preg_match('/^[a-f0-9]{32}$/', $md5);
}

function forceMD5 ($val){
    if(isValidMd5($val)) return $val;
    return md5($val);
}