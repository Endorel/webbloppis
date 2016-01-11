<?php

class UploadProduct {

	function countProducts($dbCon){

		$username = $_SESSION['username'];
		$query="SELECT user.user_id FROM user WHERE username='$username'";
		$result = $dbCon->query($query);
		$row = $result->fetch_assoc();
		$user_id=$row['user_id'];

		$query2= "SELECT count(product.product_id) AS count
			FROM product, user
			WHERE user.user_id=product.user_id
			AND user.user_id= '$user_id'
			Group by user.user_id";
		$result2 = $dbCon->query($query2);
		$row = $result2->fetch_assoc();
		$count=$row['count'];

		$query3= "SELECT membership.membership_limit 
			FROM membership, user 
			WHERE user.type_membership_id=membership.membership_name
			AND user.username='$username'";
		$result3 = $dbCon->query($query3);
		$row = $result3->fetch_assoc();
		$limit=$row['membership_limit'];

		$html="";
		$html.= "Antal produkter: ".$count. " Gräns: ".$limit;

		if($count>$limit)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function upload($dbCon){
		if(isset($_POST['submit'])){

			$username = $dbCon->real_escape_string($_SESSION['username']);
			// Kollar efter fel
			if($_FILES['file']['error'] > 0){
			   die('Fel vid uppladdning.');
			}
			//Kollar att det är en bild som laddas upp
			if(!getimagesize($_FILES['file']['tmp_name'])){
			   die('Se till att det är en bild som laddas upp.');
			}
			// Kollar filstorlek (dock verkar det inte gå att ladda upp bilder över 2 mb oavsett vilken gräns man skriver)
			if($_FILES['file']['size'] > 4096000){
			    die('Bilden överskrider maxstorleken.');
			}
			// Kollar om bilden redan finns
			if(file_exists('upload/' .$username.'/'. $_FILES['file']['name'])){
			    die('Bilden är redan uppladdad.');
			}
			// Kollar att det är rätt filtyp (png, jpg, jpeg eller gif)
			if($_FILES['file']['type'] == 'image/png' || 'image/jpg' || 'image/jpeg' || 'image/gif'){
				
				//Den uppladdade bilden placeras i mappen upload
				$uploadfile = move_uploaded_file($_FILES['file']['tmp_name'], 'upload/'.$username.'/'.$_FILES['file']['name']);
				$product_title = $dbCon->real_escape_string($_POST['title']);
				$product_text = $dbCon->real_escape_string($_POST['text']);
				$product_price = $dbCon->real_escape_string($_POST['price']);
				$product_state = $dbCon->real_escape_string($_POST['state']);
				$image_name = $username.'/'.$_FILES['file']['name'];
				$image_type = $_FILES['file']['type'];
				$image_size = $_FILES['file']['size'];
				$product_category = $dbCon->real_escape_string($_POST['category']);
				$product_subcategory = $dbCon->real_escape_string($_POST['subcategory']);
				$username=$_SESSION['username'];
				$query1="SELECT user_id FROM user WHERE username='$username'";
				$result = $dbCon->query($query1);
				$row = $result->fetch_assoc();
				$user_id = $row['user_id'];
			   	
			   	//Lägger in i databasen
				$query = ("INSERT INTO product
							(title, text, price, image_name, image_type, image_size, category, subcategory, date, user_id, state_id)
							VALUES 
							('$product_title', '$product_text', '$product_price', '$image_name', '$image_type', '$image_size', '$product_category', '$product_subcategory', CURRENT_TIMESTAMP, '$user_id', '$product_state')");
				$dbCon->query($query);
				// Uppladdning av fil
				if($uploadfile && $query){
					return "Uppladdningen lyckades!";
				}else if(!$uploadfile){
					return "Uppladdningen misslyckades";
				}else if(!$query){
					return "Bilden är inte sparad";
				}
			}
		}
	}
	//Visar hela annonsen
	function viewProductAdd($dbCon, $query){
		$html="";
		if ($result = $dbCon->query($query->showFullProductAd($dbCon)))
		{
			while ($row = $result->fetch_assoc())
			{
				$category_id=$row['category_id'];
				$subcategory_id=$row['subcategory_id'];
				$state_id=$row['state_id'];
				$user_id=$row['user_id'];
				$html = "
				<img src='upload/".$row['image_name']."' width='200' alt=''><br>".
				$row['title']." Pris: ".$row['price']." kr<br>".
				"<a href='?category=$category_id'>".$row['category_name']."</a> <a href='?subcategory=$subcategory_id'>".$row['subcategory_name']."</a><br>".
				$row['date']." ".
				"<a href='?state=$state_id'>".$row['state_name']."</a><br>".
				"<a href='?user_id=$user_id'>".$row['username']."</a><br>".
				$row['text']."<br>
				";
			}
			return $this->html = $html;
		}
		else
		{
			return "Inga annonser";
		}
	}
	//visar alla annonser som tillhör kategorien
	function viewCategory($dbCon, $query){
		$html="";
		if ($result = $dbCon->query($query->showProductsByCategory($dbCon)))
		{
			while ($row = $result->fetch_assoc())
			{
				$id=$row['product_id'];
				$html .= "".
				$row['title']." Pris: ".$row['price']." kr<br>
				<a href='?id=$id'><img src='upload/".$row['image_name']."' width='200' alt=''></a><br>
				";
			}
			return $this->html = $html;
		}
	}
	//visar alla annonser som tillhör subkategorien
	function viewSubcategory($dbCon, $query){
		$html="";
		if ($result = $dbCon->query($query->showProductsBySubcategory($dbCon)))
		{
			while ($row = $result->fetch_assoc())
			{
				$id=$row['product_id'];
				$html .= "".
				$row['title']." Pris: ".$row['price']." kr<br>
				<a href='?id=$id'><img src='upload/".$row['image_name']."' width='200' alt=''></a><br>
				";
			}
			return $this->html = $html;
		}
	}
	//visar alla annonser som tillhör län
	function viewState($dbCon, $query){
		$html="";
		if ($result = $dbCon->query($query->showProductsByState($dbCon)))
		{
			while ($row = $result->fetch_assoc())
			{
				$id=$row['product_id'];
				$html .= "".
				$row['title']." Pris: ".$row['price']." kr<br>
				<a href='?id=$id'><img src='upload/".$row['image_name']."' width='200' alt=''></a><br>
				";
			}
			return $this->html = $html;
		}
	}
	function viewProfile($dbCon, $query){
		$html="";
		if ($result = $dbCon->query($query->showProfile($dbCon)))
		{
			while ($row = $result->fetch_assoc())
			{
				$id=$row['product_id'];
				$name=$row['name'];
				$html .= "".
				$row['title']." Pris: ".$row['price']." kr<br>
				<a href='?id=$id'><img src='upload/".$row['image_name']."' width='200' alt=''></a><br>
				";
			}
			return "<h3>Välkomen till ".$name."s Loppis</h3><br>".$this->html = $html."<br>";
		}
	}
	//Visar annonsens bild, rubrik och pris – till startsidan
	function viewAddImage($dbCon, $query){
		$html="";	
		if ($result = $dbCon->query($query->showMinimizedProductAd()))
		{
			while ($row = $result->fetch_assoc())
			{
				$id=$row['product_id'];
				$html .= "".
				$row['title']." Pris: ".$row['price']." kr<br>
				<a href='?id=$id'><img src='upload/".$row['image_name']."' width='200' alt=''></a><br>
				";
			}
			return $this->html = $html;
		}
	}
}