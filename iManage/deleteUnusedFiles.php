<?php
require_once('./model/MySQL.php');
$settings = parse_ini_file('../config.ini',true);

$ftp_server = "110.4.47.56";
$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");

if (@ftp_login($ftp_conn, 'zack@easysales.asia', 'zack123@')){
    ftp_pasv($ftp_conn,true);
    foreach($settings as $config=>$value){
        if(isset($value['db'])){
            $filepath =  'public_html/'.$config.'/easysales/cms/images/product_img/';

            ftp_chdir($ftp_conn,'../../../../../');
            ftp_chdir($ftp_conn,$filepath);

            $mysql = new MySQL($value);
            $mysql->debug(true);
            $inactive_files = $mysql->Execute("SELECT * FROM cms_product_image WHERE active_status = 0");
            for ($i=0; $i < count($inactive_files); $i++) { 
                $obj = $inactive_files[$i];
                $filename = basename($obj['image_url']);
                
                if(ftp_delete($ftp_conn,$filename)){
                    echo "file deleted<br>";
                }else{
                    echo ftp_pwd($ftp_conn)." delete failed<br>";
                break;
                }
            }
        }
    }
}

ftp_close($ftp_conn);
?>