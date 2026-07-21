<?php
require_once("../config/session.php");

// Use centralized session destruction
destroySession();

// REDIRECT TO HOMEPAGE OR LOGIN PAGE
header("Location: index.php");
exit;