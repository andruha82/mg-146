<?php

ini_set("SMTP", "mindguide.online");
ini_set("smtp_port", "587"); 
ini_set("sendmail_from", "form@mindguide.online");

mail("mindguide7@gmail.com", "TEST MAIL", "HELLO. THIS IS A TEST", "From: form@mindguide.online");

?>