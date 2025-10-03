<?php
function send_mail($to,$subject,$body){
  $log=__DIR__.'/../mail.log';
  file_put_contents($log, "To:$to\nSubj:$subject\n$body\n---\n", FILE_APPEND);
}
