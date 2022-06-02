<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
hi there <br />
<?
ini_set( "didplay_errors", true );
error_reporting( ALL );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pillowan_wp541' );

/** MySQL database username */
define( 'DB_USER', 'pillowan_wp541' );

/** MySQL database password */
define( 'DB_PASSWORD', 'pS9.f)mx44' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );


$link = mysqli_connect( 'localhost', 'pillowan_wp541', 'a1357c97531' );
if ( !$link ) {
    print 'Could not connect: ' . mysqli_error() . "with no error ";
} else {
    echo 'Connected successfully';
}
mysqli_close( $link );

?>
</body>
</html>