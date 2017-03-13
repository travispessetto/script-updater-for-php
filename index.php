<?php
require_once("./langs/language.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo Language::Instance()->title; ?></title>
  <link href="css/site.css" rel="stylesheet" />
  <script src="js/jquery-3.1.1.min.js"></script>
  <?php if(file_exists("langs/js/".Language::Instance()->LanguageCode().".js")): ?>
    <script src="<?php echo "langs/js/".Language::Instance()->LanguageCode().".js"; ?>"></script>
  <?php else: ?>
    <script src="langs/js/en.js"></script>
  <?php endif; ?>
  <script src="js/site.js"></script>
</head>
<body>
<div class="header">
  <h1><?php echo Language::Instance()->title; ?></h1>
</div>
<div id="info">
  <div><?php echo Language::Instance()->check_version; ?> <span class="waiting"></span></div>
</div>
</body>
</html>
