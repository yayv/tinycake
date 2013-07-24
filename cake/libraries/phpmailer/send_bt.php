<?php

# 批量发送邮件的程序

require("class.phpmailer.php");
function sendMM(){
      global $DB_Survey;
	/* $src_url = "http://active.lvren.cn/topic/346933.html";
$page_content = file_get_contents($src_url); */
$page_content = '邀请的介绍内容 邀请你加入好友呢啊 。注册吧 ';
$max_id = 0;
$mail = new PHPMailer();

$mail->IsSMTP();                                      // set mailer to use SMTP
$mail->Host     = "mail.lvren.cn";  // specify main and backup server
$mail->SMTPAuth = true;     // turn on SMTP authentication
$mail->Username = "maillist@lvren.cn";  // SMTP username
$mail->Password = "lvrenlocalpassword12345678"; // SMTP password

$mail->From     = "maillist@lvren.cn";
$mail->FromName = "您好朋友**";

# 大飞改造程序让其一个一个地址发送

$to_arr = array(
  "chang.wei@lvren.cn",
  );
  
$mail->IsHTML(true);                                  // set email format to HTML
$mail->Subject = "旅人注册邀请";
$mail->Body    = $page_content;
$mail->AltBody = "下面的文字是由邮件中的HTML转换得到的，要浏览HTML，请切换到\"浏览HTML\"模式。如果您无法正常浏览，请您访问：http://active.lvren.cn/topic/311190.html";
$mail->AddReplyTo("maillist@lvren.cn", "旅人旅游网注册邀请");

# 开始发邮件,嘿嘿

//echo "<pre style='font-size:14px;line-height:180%'>";
$sql = "SELECT max(id) as maxid FROM `mail_send_log`";
$max_id = (int)$DB_Survey->get_var($sql); 

$sql = "SELECT * FROM `mail_send_list` where id>$max_id";
$rows = $DB_Survey->get_results($sql,'O');
if($rows){
	
	foreach($rows as $row){

		$mail->Body = str_replace("</html>","<img src=\"http://image.lvren.cn/icon/weekend/count.gif?sid=".$row->sid."&email=".$row->email."\"></html>",$page_content);

		//echo $row->Email;
		$mail_value = trim($row->email);
		//$mail_value = "adolph007@sohu.com";
		//echo "dafei".__LINE__." ".$mail_value."\r\n";
		#$mail->to = array();
		$mail->AddAddress($mail_value);                  // name is optional
		
		 
		#$mail->WordWrap = 50;                                 // set word wrap to 50 characters
		#$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
		#$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
		
		
		if(!$mail->Send())
		{
		   //echo $mail_value." X, ".$mail->ErrorInfo." \r\n";
		   #echo "Message could not be sent. <p>";
		   #echo "Mailer Error: " . $mail->ErrorInfo;
		   #exit;
		   $result = 0;
		}else{
		   $result = 1;
		   echo $row->sid." ".$mail_value." OK\r\n";
		}
		
		$sql = "INSERT INTO `mail_send_log` (`id`, `result`, `send_time`, `int_send_time`) VALUES ('".$row->id."', '$result', '".date("Y-m-d H:i:s",time())."', '".time()."')";
		//$DB_Survey->query($sql);

		if(!($mail_key%2)){
		   //echo $mail_key."....".date("Y-m-d H:i:s")."\r\n";
		}

		$max_id = $row->id;
	}# end of foeach 
}
  $status =  date("Y-m-d H:i:s")."\r\n";
  return $status;
}

?>