<?php
require_once __DIR__ . '/../src/qr/qrlib.php';

$mensagem = "";
$imagemQR = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $link = trim($_POST["link"]);

    if (empty($link)) {

        $mensagem = "Introduza um link.";

    } else {

        $pasta = __DIR__ . "/qr_codes/";

        if (!file_exists($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $nome = "qr_" . time() . ".png";

        QRcode::png($link, $pasta . $nome);

        $imagemQR = "qr_codes/" . $nome;

        $mensagem = "QR Code criado com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Gerador de QR Code</title>

<style>

body{
    font-family:Arial;
    margin:40px;
    background:#f2f2f2;
}

.container{
    background:white;
    padding:30px;
    width:500px;
    margin:auto;
    border-radius:10px;
}

input{
    width:100%;
    padding:10px;
}

button{
    margin-top:15px;
    padding:10px 20px;
}

img{
    margin-top:20px;
}

</style>

</head>

<body>

<div class="container">

<h2>Gerador de QR Code</h2>

<form method="post">

<label>Link</label>

<input
type="url"
name="link"
placeholder="https://www.google.com"
required>

<button type="submit">
Gerar QR Code
</button>

</form>

<?php

if($mensagem!=""){
    echo "<p>$mensagem</p>";
}

if($imagemQR!=""){
    echo "<img src='$imagemQR'>";
}

?>

</div>

</body>
</html>