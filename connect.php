
$localhost = "119.59.120.5";
$username = "exvaycom_linebot";
$password = "!1q2w3e4r5t";
$dbname = "exvaycom_line";

$conn = mysql_connect($localhost,$username,$password)or die("Can't Connect to Server");
//mysql_query("SET NAMES tis620");
mysql_select_db($dbname);

/*
$cmsql = "SELECT * FROM telephone where nickname = 'toy'";
$cmquery = mysql_query($cmsql)or die("Can't Query ".mysql_error() . " Actual query: " . $cmsql);
$cmchknum = mysql_num_rows($cmquery);
$objResult = mysql_fetch_array($cmquery);
if ($cmchknum > 0) {
	echo "Found!"."<br/>";
	echo "Telephone Number is : ".$objResult["tel_no"];
}else{
	echo "NOT Found!";
}
*/


