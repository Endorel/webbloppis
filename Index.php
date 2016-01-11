<?php
session_start();

//LÄSER IN KLASSER
function __autoload($class_name) {
	include 'classes/'.$class_name.'.php';
}

//DATABASKOPPLINGEN
$dbCon = Connection::connect();

//NYA OBJEKT
$user = new User();
$print = new PrintPage();
$upload = new UploadProduct();
$query = new Query();
$mail = new ValidateMail();

mysqli_query($dbCon, "SET NAMES 'utf8'") or die(mysql_error());
mysqli_query($dbCon, "SET CHARACTER SET 'utf8'") or die(mysql_error()); 

$sender = $mail->valSendername();
$subject = $mail->valSubject();
$message = $mail->valMessage();
$senderemail = $mail->valSenderemail();
$receiveremail = $mail->valReceiveremail();

$upload->upload($dbCon);


//OM MAN HAR TRYCKT PÅ LOGIN KNAPP VISAS DET LOGIN FORMULÄR
if (isset($_POST['login'])) {
	$user->login($dbCon);
}

//OM MAN HAR TRYCKT PÅ LOGOUT KNAPP VISAS DET TITLE, LOGOUT MEDDELANDE OCH LOGIN FORMULÄR
if (isset($_POST['logout'])) {
	$data = array(
		'title' => 'Webbloppis',
		'logoutMsg' =>$user->logout(),
		'loginForm' =>$print->printLoginForm(),
	);
}

//OM SESSION HAR SATT VISAS DET TITLE, USERS NAMN OCH LOGOUT FORMULÄR
if (isset($_SESSION['username'])) {
	$data = array(
		'title' => 'Webbloppis',
		'name' =>$print->printName($dbCon),
		'logoutForm' =>$print->printLogoutForm(),
		'newProductForm' =>$print -> newProductForm($dbCon),
		'addProduct' =>$upload->upload($dbCon),
		'uploadProduct' =>$upload->upload($dbCon),
		'showProductsForm' =>$print->printShowProductsForm(),
		'countProducts' => $upload -> countProducts($dbCon)
	);

	//Visar en viss säljares annonser
	if(isset($_POST['showProducts'])){
		$data = array(
		'title' => 'Webbloppis',
		'viewPersonalAds' => $print->viewPersonalAds($dbCon,$query),
		'goBackToFirstLoggedInPage' => $print->printGoBackFromShowProductsForm()
		);
	}
}

//DET VISAS HELA ANNONSEN
elseif(isset($_GET['id'])){
	$data = array(
		'title' => 'Webbloppis',
		'viewProductAd' => $upload->viewProductAdd($dbCon, $query),
		'openMailform' =>$print->openMailform(),
	);
	//DET VISAS MEJLFORMULÄRET
	if (isset($_POST['sendmail'])) {
		$data = array(
			'title' => 'Webbloppis',
			'printMailform'=>$print->printMailform()
		);
	}
	//DET SKICKAR ETT MEJL TILL SÄLJAREN
	if (isset($_POST['send'])) {
		mail($receiveremail, $subject, $message, 'From: ' . $senderemail);
	}
	
}
//DET VISAS ALLA ANNONSER SOM TILLHÖR EN KATEGORI
elseif (isset($_GET['category'])) {
	$data = array(
		'title' => 'Webbloppis',
		'viewCategory' =>$upload->viewCategory($dbCon, $query)
	);
}
//DET VISAS ALLA ANNONSER SOM TILLHÖR EN SUBKATEGORI
elseif (isset($_GET['subcategory'])) {
	$data = array(
		'title' => 'Webbloppis',
		'viewSubcategory' =>$upload->viewSubcategory($dbCon, $query)
	);
}
//DET VISAS ALLA ANNONSER SOM TILLHÖR EN LÄN
elseif (isset($_GET['state'])) {
	$data = array(
		'title' => 'Webbloppis',
		'viewState' =>$upload->viewState($dbCon, $query)
	);
}
//DET VISAS ALLA ANNONSER SOM TILLHÖR EN SÄLJARE
elseif (isset($_GET['user_id'])) {
	$data = array(
		'title' => 'Webbloppis',
		'viewProfile' =>$upload->viewProfile($dbCon, $query),
		'openMailform' =>$print->openMailform(),
	);
	//DET VISAS MEJLFORMULÄRET
	if (isset($_POST['sendmail'])) {
		$data = array(
			'title' => 'Webbloppis',
			'printMailform'=>$print->printMailform()
		);
	}
	//DET SKICKAR ETT MEJL TILL SÄLJAREN
	if (isset($_POST['send'])) {
		
	}
}

//ANNARS VISAS DET TITLE OCH LOGIN FORMULÄR
else {
	$data = array(
		'title' => 'Webbloppis',
		'loginForm' =>$print->printLoginForm(),
		'viewAddImage' => $upload->viewAddImage($dbCon, $query),
		'searchForm' => $print->searchProductForm($dbCon)
	);
}

//OM MAN HAR TRYCKT PÅ CREATE NEW ACCOUNT KNAPP VISAS DET ETT FORMULÄR FÖR ATT SKAPA ETT KONTO
if (isset($_POST['newAccount'])) {
	$data = array(
		'createAccountForm' =>$print->createAccountForm($dbCon)
	);
}

//SKAPA ETT KONTO
if (isset($_POST['createAccount'])){
	$user->createAccount($dbCon);
}

//Om man har klickat på sök-knappen visas sökresultatet
if (isset($_POST['searchProduct'])){
	$data = array(
		'title' => 'Webbloppis',
		'searchResult' => $print->searchResult($dbCon, $query),
	);
}


//Läser in Twig
require_once 'Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader);
echo $twig->render('index.html', $data);

