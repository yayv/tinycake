<?php     
/**
 * @Description: 重写的session handler 函数
 *    用 sessid 作为 key, value就是整个session内容
 *    
 *  
 */

/*
//===============================================
Database Contructure
//-----------------------------------------------

CREATE TABLE IF NOT EXISTS `sns_session` (
  `id` char(32) NOT NULL DEFAULT '',
  `uid` int(11) DEFAULT NULL,
  `ip` char(15) NOT NULL,
  `value` text,
  `dateline` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
//===============================================

//===============================================
Config Example:
//-----------------------------------------------

$sessconfig = array();
$sessconfig['HOST'] = 'dbserver_sns';
$sessconfig['DATABASE'] = 'sns';
$sessconfig['USER'] = 'LVRN';
$sessconfig['PASSWORD'] = 'aCim3)J9n$M';
$sessconfig['LINKNAME'] = 'con_sns';

//重定义session handler
require_once(dirname(__FILE__).'/public_dbclass.php');

ini_set("session.save_handler", 'user');

session_set_save_handler(
        "sess_open", "sess_close", "sess_read", 
        "sess_write", "sess_destroy", "sess_gc");
//-----------------------------------------------

*/

  
// set handler
function sess_open($save_path, $session_name)
{
  // connect database
  global $sessconfig;
  if(!isset($sessconfig)) die('no configure!');

  $conn = mysql_connect($sessconfig['HOST'], $sessconfig['USER'], $sessconfig['PASSWORD']);
  mysql_select_db($sessconfig['DATABASE'], $conn);
  $sessconfig['conn'] = $conn;

  return(true);
}
 
function sess_close()
{
  // disconnect database
  global $sessconfig;
  mysql_close($sessconfig['conn']);
  unset($sessconfig);

  return(true);
}

function sess_read($id)
{
  // read from database
  global $sessconfig;
  
  $sql = "select value from sns_session where id='$id'";
  $res =  mysql_query($sql, $sessconfig['conn']);
  $record = mysql_fetch_array($res);
  $ret    = $record['value'];

  return $ret;
}

function sess_write($id, $sess_data)
{
  // write data to database 
    
  // 这里需要重新连接，原因是全局对象会被销毁
  global $sessconfig;
  if(!isset($sessconfig)) die('no configure!');

  if(!isset($sessconfig['conn'])) 
  {
    $sessconfig['conn'] = mysql_connect($sessconfig['HOST'],$sessconfig['USER'], $sessconfig['PASSWORD']);
    mysql_select_db($sessconfig['DATABASE']);
  }  
  
  $sql = "select id from sns_session where id='$id'";
  
  $ret = mysql_query($sql, $sessconfig['conn']);
  $RecordCount = mysql_num_rows($ret);
  if($ret && $RecordCount>0)
      $t = mysql_fetch_array($ret);
  
  $uid = isset($_SESSION['user_id'])?$_SESSION['user_id']:-1;
  if($RecordCount==1)
  {
      if('127.0.0.1'==$_SERVER['REMOTE_ADDR'])
          $ip = '1=1';
      else
          $ip  = "ip='".$_SERVER['REMOTE_ADDR']."'";
           
      $now = time();
      $sql = "update sns_session 
                  set dateline=$now, value='$sess_data', uid=$uid, $ip 
              where id='$id'";
      mysql_query($sql, $sessconfig['conn']);
  }
  else
  {
      if($RecordCount>1)
      {
          $sql = "delete from sns_sessioin where id='$id'";
          $ret = mysql_query($sql, $sessconfig['conn']);
      }
  
      $ip  = $_SERVER['REMOTE_ADDR'];
      $now = time();
      $sql = "insert into sns_session(id, uid, ip,value, dateline) 
              values('$id', $uid, '$ip', '$sess_data', $now) ";
      $ret = mysql_query($sql, $sessconfig['conn']);
  }
  
  return true;
}

function sess_destroy($id)
{
    // delete from database
    global $sessconfig;
    $sql = "delete from sns_sessioin where id='$id'";
    $ret = mysql_query($sql, $sessconfig['conn']);
    mysql_close($sessconfig['conn']);    
    unset($sessconfig['conn']);

    return true;
}

function sess_gc($maxlifetime)
{
    // do nothing
    return true;
}


