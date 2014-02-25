<?php

$error = null;

function get_members_from_sql () {
  global $error;

  if (is_readable ("/home/admin/secret/anonvoting")) {
    include ("/home/admin/secret/anonvoting");
  } else {
    $error = "Can not get the authentication data.";
    return FALSE;
  }

  $members_table = "foundationmembers";

  $handle = mysql_connect ("$mysql_host", "$mysql_user", "$mysql_password");
  if (!$handle) {
    $error = "Can not connect to the database.";
    return FALSE;
  }
  $result = mysql_query ("SET NAMES 'utf8'", $handle);
  if (!$result) {
    die ("Unable to run query: ".mysql_error ($handle));
  }

  $select_base = mysql_select_db ($mysql_db, $handle); 
  if (!$select_base) {
    mysql_close ($handle);
    $error = "Can not select the database.";
    return FALSE;
  }

  $query = "SELECT firstname, lastname, email, last_renewed_on FROM ".$members_table;
  $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 2 YEAR) <= last_renewed_on";
  $query .= " ORDER BY lastname, firstname";

  $result = mysql_query ($query, $handle);

  if ($result === FALSE) {
    $error = mysql_error ($handle);
    $retval = FALSE;
  } else {
    $result_array = array ();
    while ($buffer = mysql_fetch_assoc ($result)) {
      $result_array[] = $buffer;
    }
    $retval = $result_array;
  }

  mysql_close ($handle);
  return $retval;
}

$members = get_members_from_sql ();


if (isset ($_GET['format'])) {
  $format = $_GET['format'];
} else {
  $format = 'html';
}


if ($format == 'json') {
  if ($members === FALSE) {
    echo json_encode (array ('error' => $error));
  } else {
    echo json_encode ($members);
    exit;
  }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

  <head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><link rel="stylesheet" type="text/css" href="https://static.gnome.org/css/vote/default.css" /><link rel="stylesheet" type="text/css" href="https://static.gnome.org/css/vote/foundation.css" /><link rel="icon" type="image/png" href="https://static.gnome.org/img/logo/foot-16.png" />
    <title>GNOME Foundation Membership List</title>
    <meta name="cvsdate" content="$Date$" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>

  <body><div id="body">

    <h1>GNOME Foundation Membership List</h1>

    <p>
      Send comments, questions, and updates to <a
      href="mailto:membership-committee&#64;gnome&#46;org">membership-committee&#64;gnome&#46;org</a>.
    </p>
    
<?php

if ($members === FALSE) {
  echo "<p>Error: ".$error.".</p>\n";
} else {
  echo "    <ul>\n";
  $antispam = array(".", "@");

  foreach ($members as $member) {
    $email = str_replace($antispam, " ", $member["email"]);
    echo "      <li title='Last Renewed On: ".$member['last_renewed_on']."'>".$member["firstname"]." ".$member["lastname"]." &lt;".$email."&gt;</li>\n";
  }
  echo "    </ul>\n";
}
?>
 
</div>

<div id="hdr">
<div id="logo"><a href="http://foundation.gnome.org/"><img src="https://static.gnome.org/img/spacer.png" alt="Home" /></a></div>
<div id="banner"><img src="https://static.gnome.org/img/spacer.png" alt="" /></div>
<p class="none"></p>
<div id="hdrNav">
<a href="https://www.gnome.org/about/">About GNOME</a> &middot;
<a href="https://www.gnome.org/start/stable/">Download</a> &middot;
<!--<a href="http://www.gnome.org/contribute/"><i>Get Involved!</i></a> &middot;-->
<a href="https://www.gnome.org/">Users</a> &middot;
<a href="https://help.gnome.org/devel">Developers</a> &middot;
<a href="https://foundation.gnome.org/"><b>Foundation</b></a> &middot;
<a href="https://www.gnome.org/contact/">Contact</a>
</div>
</div>
</body>

</html>
