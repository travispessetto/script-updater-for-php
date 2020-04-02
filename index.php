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
  <script src="js/site.js?time=<?php echo time(); ?>"></script>
</head>
<body>
<div class="header">
  <h1><?php echo Language::Instance()->title; ?></h1>
  <h6><a href="https://github.com/travispessetto/script-updater-for-php"><?php echo Language::Instance()->author; ?></a></h6>
</div>
<div id="info">
  <div><?php echo Language::Instance()->welcome_message; ?> <span class="waiting"></span></div>
</div>
<div id="count">
    <div>
      <a href="https://www.hitwebcounter.com" target="_blank">
      <img src="https://hitwebcounter.com/counter/counter.php?page=7165217&style=0007&nbdigits=5&type=ip&initCount=0" title="Counter For Website Hitwebcounter" Alt="hitwebcounter.com"   border="0" >
      </a>
    </div>
</div>   
</body>
</html>
