<?php
// setcookie aplica urlencode
setcookie('name', 'João Silva'); // cookie armazenado como: Jo%C3%A3o+Silva

// setrawcookie NÃO aplica urlencode (você é responsável)
setrawcookie('token', rawurlencode('abcd/xyz'));
