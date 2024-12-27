<div class="max-cont-width">
<h1 class="newsheader">Новости нашей ассоциации</h1>
<?php
MG::enableTemplate();
MG::titlePage('Новости таврической ассоциации');
mgAddMeta('<link href="mg-plugins/news/css/style.css" rel="stylesheet" type="text/css">');
if (class_exists('PluginNews')) {
  PluginNews::runNews(3);
} else {
   echo "Плагин новостей не подключен!";
}?>
</div>

