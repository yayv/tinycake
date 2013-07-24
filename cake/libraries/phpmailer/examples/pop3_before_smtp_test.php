<html>
<head>
<title>POP before SMTP Test</title>
</head>

<body>

<pre>
<?php
  require '../class.phpmailer.php';
  require '../class.pop3.php';

  $pop = new POP3();
  $pop->Authorise('mail.lvren.cn', 110, 30, 'maillist', 'lvrenlocalpassword12345678', 1);

  $mail = new PHPMailer();

  $mail->IsSMTP();
  $mail->SMTPDebug = 2;
  $mail->IsHTML(false);

  $mail->Host     = 'mail.lvren.cn';

  $mail->From     = 'wu.junfei@lvren.cn';
  $mail->FromName = 'Example Mailer';

  $mail->Subject  =  'My subject';
  $mail->Body     =  'Hello world';
  $mail->AddAddress('mail.lvren.cn', 'First Last');

  if (!$mail->Send())
  {
    echo $mail->ErrorInfo."大飞提示";
  }else{
    echo "OK";
  }
?>
</pre>

</body>
</html>
