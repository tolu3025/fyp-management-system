<?php
// register.php
// Legacy registration endpoint redirecting to the unified sliding auth gateway.

header("Location: login.php?mode=register");
exit();
