<?php

# 批量发送邮件的程序

require("class.phpmailer.php");
require("../public_connect.php");

$src_url = "http://active.lvren.cn/topic/346933.html";
$page_content = file_get_contents($src_url);
$max_id = 0;
$mail = new PHPMailer();

$mail->IsSMTP();                                      // set mailer to use SMTP
$mail->Host     = "mail.lvren.cn";  // specify main and backup server
$mail->SMTPAuth = true;     // turn on SMTP authentication
$mail->Username = "chang.wei@lvren.cn";  // SMTP username
$mail->Password = "123456"; // SMTP password

$mail->From     = "maillist@lvren.cn";
$mail->FromName = "旅人中国周末建议";

# 大飞改造程序让其一个一个地址发送

$to_arr = array(
  "chang.wei@lvren.cn",
  );
  
$mail->IsHTML(true);                                  // set email format to HTML
$mail->Subject = "恋恋夏日 我和草原有个约定";
$mail->Body    = $page_content;
$mail->AltBody = "七、八月间是草原上最美丽的时节，每到这时，草原就热闹起来，篝火晚会、骑马、射击、射箭等娱乐项目应有尽有，你还可以品尝到烤全羊、手扒肉、奶茶、奶豆腐、莜面等风味食品，旅人为你准备了贴心的坝上草原攻略……我们的邮件为HTML格式，要浏览HTML，请切换到\"浏览HTML\"模式。如果您无法正常浏览，请您访问：$src_url";
$mail->AddReplyTo("maillist@lvren.cn", "旅人中国周末推荐");

# 开始发邮件,嘿嘿

while(true){
$sql = "SELECT id,sid,count('x') as count,email,src_url,host FROM `mail_send_list` group by host order by count desc ";
$rows = $DB_Nuser->get_results($sql,'O');
if($rows){
	
	if(sizeof($rows)>1){

		foreach($rows as $row){

			$mail->Body = str_replace("</html>","<img src=\"http://image.lvren.cn/icon/weekend/count.gif?w=23&sid=".$row->sid."&email=".$row->email."\"></html>",$page_content);

			$result = 1;
			$mail_value = trim($row->email);
			$mail->AddAddress($mail_value);                  // name is optional			
			
			if(!$mail->Send())
			{
			   $result = 0;
			}else{
			   $result = 1;
			   echo $row->sid." ".$mail_value." OK\r\n";
			}
			
			$sql = "INSERT INTO `mail_send_log` (`id`, `result`, `send_time`, `int_send_time`) VALUES ('".$row->id."', '$result', '".date("Y-m-d H:i:s",time())."', '".time()."')";
			$DB_Nuser->query($sql);

			$sql = "delete from `mail_send_list` where id=".$row->id;
			$DB_Nuser->query($sql);
			
			if(!($mail_key%2)){
			   //echo $mail_key."....".date("Y-m-d H:i:s")."\r\n";
			}

			$max_id = $row->id;
		}# end of foeach
		//echo "\r\n".sizeof($rows);

	}else{
		$sql = "SELECT * FROM `mail_send_list`";
		$rows = $DB_Nuser->get_results($sql,'O');
		if($rows){
			foreach($rows as $row){
				$mail->Body = str_replace("</html>","<img src=\"http://image.lvren.cn/icon/weekend/count.gif?w=23&sid=".(int)$row->sid."&email=".$row->email."\"></html>",$page_content);

				$result = 1;
				$mail_value = trim($row->email);
				$mail->AddAddress($mail_value);                  // name is optional			
				
				if(!$mail->Send())
				{
				   $result = 0;
				   echo $mail->ErrorInfo;
				}else{
				   $result = 1;
				   echo $row->sid." ".$mail_value." OK\r\n";
				}
				
				$sql = "INSERT INTO `mail_send_log` (`id`, `result`, `send_time`, `int_send_time`) VALUES ('".$row->id."', '$result', '".date("Y-m-d H:i:s",time())."', '".time()."')";
				$DB_Nuser->query($sql);

				$sql = "delete from `mail_send_list` where id=".$row->id;
				$DB_Nuser->query($sql);
				
				if(!($mail_key%2)){
				   //echo $mail_key."....".date("Y-m-d H:i:s")."\r\n";
				}

				$max_id = $row->id;

				$seedarray =microtime(); 
				$seedstr =split(" ",$seedarray,5); 
				$seed =$seedstr[0]*10000; 

				srand($seed); 

				$random =rand(0,5); 
				$sleep = 10 + $random;
				sleep($sleep);
			}
		}
	}
	sleep(15);
	echo "sleep10\r\n";
}
}
echo date("Y-m-d H:i:s")."\r\n";

?>