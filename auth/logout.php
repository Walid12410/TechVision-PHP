<?php
setcookie("token", "", time() - 3600, "/", "", false, true);
echo json_encode(["message" => "Logged out"]);

?>