<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Document</title>
</head>
<body>
<form id="monForm1" action="main.php" method="post">
	<fieldset>
		<legend>Connexion employe</legend>
		<p>
			<label>Login</label><input required type="text" name="login"/>
		</p>

		<p>
			<label>Mot de passe</label><input required type="password" name="motdepasse"/>
		</p>

		<p>
			<input type="submit" name="connexion" value="Se connecter"/>
		</p>
        <?php echo $contenuErr ?>

	</fieldset>
</form>
</body>
</html>