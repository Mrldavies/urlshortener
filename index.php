<?php require("connection.php");
	$urlError = null;
	$shortURL = null;


	if(isset($_GET['url'])){
		// If short URL exists in database
		$stmt = $con->prepare("SELECT * FROM url WHERE short = :short");				
		$stmt->bindparam(":short", $_GET['url']);
		$stmt->execute();
		$shortExists = $stmt->rowcount();
		if($shortExists == 1){
		$row = $stmt->fetch();
			header("location: " . $row[url] . "  ");
		}

	}

function shortHash($con){
	// Loops hash if not in database
	$exists = true;
 	while ($exists) {
	//Generate Random string
		$random = rand(0,9999). time();
		$hash = hash("sha512", $random);
		$short = substr($hash, 0,6);
	//Checks if hashing is in database	
		$stmt = $con->prepare("SELECT short FROM url WHERE short = :short");				
		$stmt->bindparam(":short", $short);
		$stmt->execute();
		$shortExists = $stmt->rowcount();
	// If hash is not in database end loop
		if($shortExists == 0){
 			$exists = false;
 			return $short;
		}
	}
}
if(isset($_POST['submit'])){
	$error = 0;
	$url = $_POST['url'];

	if($url == null){
		$error++;
		$urlError = "Please enter a url";
	}

  	
	if($error == 0){
		// Executes shortHash function to create the Short URL
		$short = shortHash($con);
		
		// Checks if URL is already in database	
		$stmt = $con->prepare("SELECT * FROM url WHERE url = :url");				
		$stmt->bindparam(":url", $url);
		$stmt->execute();
		$urlExists = $stmt->rowcount();
		// If URL is in the database display Short URL.
		if($urlExists == 1){
			$row = $stmt->fetch();
			$shortURL = "<a href=\"/" . $row['short'] . "\">" . $_SERVER['HTTP_HOST'] . "/" . $row['short'] . "</a>";
		// If URL doesn't exisit insert into database.	
		}else{
			$stmt = $con->prepare("INSERT INTO url (url, short)VALUES(:url, :short)");
			$stmt->bindparam(":url", $url);
			$stmt->bindparam(":short", $short);
			$stmt->execute();
			$shortURL = "<a href=\"/" . $short . "\">" . $_SERVER['HTTP_HOST'] . "/" . $short . "</a>";
		}
	}
}

?>
<!Doctype html>
<html>
	<head>
	<title>Short URL</title>
	</head>
	<body>
	<?php echo $urlError . $shortURL; ?>
	<form method="post" action="">
	<label>Paste URL: </label>
	<input type="text" name="url">
	<input type="submit" name="submit" value="Generate URL">
	</form>
	</body>
</html>
