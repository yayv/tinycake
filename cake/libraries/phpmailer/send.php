<?php

# 批量发送邮件的程序

require("class.phpmailer.php");
function sendMM($from){
	global $DB_Survey;
	/* $src_url = "http://active.lvren.cn/topic/346933.html";
	$page_content = file_get_contents($src_url); */
	$page_content = '邀请的介绍内容 邀请你加入好友呢啊 。注册吧 ';
	$max_id = 0;
	$mail = new PHPMailer();

	$mail->IsSMTP();  // set mailer to use SMTP
	$mail->Host     = "mail.lvren.cn";  // specify main and backup server
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = "maillist@lvren.cn";  // SMTP username
	$mail->Password = "lvrenlocalpassword12345678"; // SMTP password

	$mail->From     = "maillist@lvren.cn";
	$mail->FromName = "您好朋友**";

	$mail->IsHTML(true);                                  // set email format to HTML
	$mail->Subject = "旅人注册邀请";
	$mail->Body    = $page_content;
	$mail->AltBody = "下面的文字是由邮件中的HTML转换得到的，要浏览HTML，请切换到\"浏览HTML\"模式。如果您无法正常浏览，请您访问：http://www.lvren.cn";
	$mail->AddReplyTo("maillist@lvren.cn", "旅人旅游网注册邀请");

	# 开始发邮件,嘿嘿

	$mail->Body = str_replace("</html>","<img src=\"http://image.lvren.cn/icon/weekend/count.gif?sid=".$row->sid."&email=".$row->email."\"></html>",$page_content);

	$mail_value = trim($from);

	$mail->AddAddress($mail_value);                  // name is optional


	if(!$mail->Send())	{

		$result = 0;
	}else{
		$result = 1;
		echo $row->sid." ".$mail_value." OK\r\n";
	}

	$sql = "INSERT INTO `mail_send_log` (`id`, `result`, `send_time`, `int_send_time`) VALUES ('".$row->id."', '$result', '".date("Y-m-d H:i:s",time())."', '".time()."')";
	$DB_Survey->query($sql);

	if(!($mail_key%2)){
		//echo $mail_key."....".date("Y-m-d H:i:s")."\r\n";
	}

	$max_id = $row->id;

	$status =  date("Y-m-d H:i:s")."\r\n";
	return $status;
}

?>