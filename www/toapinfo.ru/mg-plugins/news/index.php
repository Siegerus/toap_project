<?php
/*
  Plugin Name: Новостная лента с Яндекс.Турбо
  Description: Позволяет вести новостную ленту добавляя и редактируя тексты новостей. После подключения плагина становится доступной страница /news , на которой отображается список анонсов всех новостей. А также появляется возможность подписаться на RSS рассылку по адресу /news/feed
  Author: Avdeev Mark
  Version: 3.1.22
  Edition: CLOUD
 */

/**
 * При активации плагина, создает таблицу для новостей
 * также создает файл news.php , который будет генерироватьодноименную страницу сайта
 * [sitename]/news.html, при необходимости его можно изменять.
 * На данной странице будут выведены анонсы новостей.
 */
new PluginNews();

class PluginNews {

    private static $pluginName = ''; // название плагина (соответствует названию папки)
    private static $path = ''; //путь до файлов плагина

    public function __construct()
    {
        mgActivateThisPlugin(__FILE__, array(__CLASS__, 'createDateBaseNews'));
        mgAddAction(__FILE__, array(__CLASS__, 'pagePluginNews'));
        mgAddAction('mg_gethtmlcontent', array(__CLASS__, 'printNews'), 1);
        mgAddAction('mg_start', array(__CLASS__, 'newsFeed'));
        mgAddShortcode('news-anons', array(__CLASS__, 'anonsNews'));
        self::$pluginName = PM::getFolderPlugin(__FILE__);

        $explode = explode(str_replace('/', DS, PLUGIN_DIR), dirname(__FILE__));
        if (strpos($explode[0], 'mg-templates') === false) {
            self::$path = str_replace('\\', '/', PLUGIN_DIR.DS.$explode[1]);
        } else {
            $templatePath = str_replace('\\', '/', $explode[0]);
            $templatePathParts = explode('/', $templatePath);
            $templatePathParts = array_filter($templatePathParts, function($pathPart) {
                if (trim($pathPart)) {
                return true;
                }
                return false;
            });
            $templateName = end($templatePathParts);
            self::$path = 'mg-templates/'.$templateName.'/mg-plugins/'.$explode[1];
        }


        if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
            mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/anons.css" type="text/css" />');
        }
    }

    public static function createDateBaseNews()
    {
        DB::query("
            CREATE TABLE IF NOT EXISTS  `" . PREFIX . "mpl_news` (
                `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                `title` VARCHAR( 255 ) NOT NULL ,
                `description` longtext NOT NULL,
                `add_date` DATETIME NOT NULL ,
                `url` VARCHAR( 255 ) NOT NULL ,
                `image_url` VARCHAR( 255 ) NOT NULL ,
                `meta_title` varchar(255) NOT NULL,
                `meta_keywords` varchar(512) NOT NULL,
                `meta_desc` text NOT NULL,
                `author` text NOT NULL,
            PRIMARY KEY ( `id` )
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
        ");

        setOption('countPrintRowsNews', 10);

        if (EDITION != 'saas') {
            $newsPagePath = PLUGIN_DIR . 'news/viewnews.php';
            $newsPageNewPath = TEMP_DIR . 'news.php';
            if (!file_exists($newsPageNewPath)) {
                copy($newsPagePath,  $newsPageNewPath);
            }
        }
    }

    //Выводит полную новость на странице news/[название_новости]
    public static function printNews($arg)
    {
        $result = $arg['result'];
        if (URL::isSection('news')) {
            $arrSections = URL::getSections();
            $news = self::getNewsByUrl(URL::getLastSection());
            if (empty($news) && count($arrSections) < 3) {
                $newsContent = TEMP_DIR . 'news.php';
                if (EDITION == 'saas') {
                    $newsContent = PLUGIN_DIR . 'news/viewnews.php';
                }
                ob_start();
                include($newsContent);
                $result .= ob_get_contents();
                ob_end_clean();
                return $result;
            }
            if (count($arrSections) > 3) {
                MG::redirect('/404.html');
            }
            MG::titlePage($news['title']);
            MG::seoMeta($news);
            $realDocumentRoot = URL::getDocumentRoot();

            if (file_exists($realDocumentRoot . 'mg-pages' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . 'news.item.php')) {

                $template = $realDocumentRoot . 'mg-pages' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . 'news.item.php';
                ob_start();
                include($template);
                $result .= ob_get_contents();
                ob_end_clean();
            } else {
                $img = $news['image_url'] ?
                    '<img src="' . SITE . '/uploads/news/' . $news['image_url'] . '" alt="' . $news['title'] . '" title="' . $news['title'] . '">' : '';
                $result = '  <div class="max-cont-width">
                    <div class="main-news-block news-post">
                        <a href="' . SITE . '" class="go-back-link">&larr; Назад</a>
                        <div class="main-news-item">             
                            <h1 class="news-title sect-title">' . $news['title'] . '</h1>
                            <span class="news-date">' . date('d.m.Y', strtotime($news['add_date'])) . '</span>
                            <div class="clear"></div>
                            <div class="main-news-img">
                            ' . $img . '  
                            </div>
                            <div class="post-description mc__about-project_cont ">
                             <h2 class="big-title">Новости ассоциации</h2><p>
                            ' . MG::inlineEditor(PREFIX . "mpl_news", "description", $news['id'], $news['description'], 'news'.DS.$news['id'], null, true) . '
                            </p></div>                 
                        </div>
                        </div>

                    </div>';
            }
        }
        return $result;
    }

    // Формирует и выводит RSS ленту.
    public static function newsFeed()
    {
        if (URL::getClearUri() == '/news/feed') {
            MG::disableTemplate();
            include 'feed.php';
            $rss = new Feed(SITE, 'RSS подписка на новости', 'Все о moguta.CMS');
            $data = self::getListNews();
            $listNews = $data['listNews'];
            foreach ($listNews as $news) {
                $rss->AddItem(
                    htmlentities(SITE . '/news/' . $news['url']), $news['title'], $news['description'], $news['add_date'], $news['author'], $news['image_url']
                );
            }

            # публикуем рузельтирующий RSS 2.0
            $rss->Publish();
            exit;
        }
    }

    //выводит страницу плагина в админке
    public static function pagePluginNews()
    {
        $lang = PM::plugLocales('news');
        if ($_POST["page"])
            $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс

        $countPrintRowsNews = MG::getOption('countPrintRowsNews');

        $navigator = new Navigator("SELECT  *  FROM `" . PREFIX . "mpl_news` ORDER BY `add_date` DESC", $page, $countPrintRowsNews); //определяем класс
        $news = $navigator->getRowsSql();
        $pagination = $navigator->getPager('forAjax');

        echo '<link href="' . SITE . '/mg-plugins/news/css/style.css" rel="stylesheet" type="text/css">';

        // подключаем view для страницы плагина
        include 'pagePlugin.php';
    }

    /**
     * Печатает на экран анонс заданной новости
     * @param type $news - массив с данными о новости (полностью запись из БД)
     */
    public static function printAnonsNews($news)
    {
        ?>

        <div class="mc-n-c__card ">
           
             <a  href="<?php echo SITE ?>/news/<?php echo $news['url']; ?>"
                   title="<?php echo $news['title']; ?>">
                <p class="">
               
                    <?php echo $news['title']; ?>
                
                </p>
                <a href="<?php echo SITE ?>/news/<?php echo $news['url']; ?>">
                <svg class="icon" width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"></path>
                </svg>
                <span><?php echo date('d.m.Y', strtotime($news['add_date'])); ?> </span>
              </a>
            </a>
        </div>

        <?php
    }

    /**
     * Печатает на экран анонс заданной новости
     * @param type $news - массив с данными о новости (полностью запись из БД)
     */
    public static function anonsNews($args)
    {
        $args['count'] = $args['count'] ? $args['count'] : 3;
        $data = self::getListNews($args['count'], false);
        $listNews = $data['listNews'];
        $html = '
    <section class="mc__news">
    <h1>Новости</h1>

      <div class="mc__news-cont">';

        if (!empty($listNews)) {
            foreach ($listNews as $news) {
                $imagePrefix = '';
                if (file_exists(SITE_DIR.'uploads'.DS.'news'.DS.'thumbs'.DS.'70_'.$news['image_url'])) {
                    $imagePrefix = 'thumbs/70_';
                }
                $img = $news['image_url'] ? '<a href="' . SITE . '/news/' . $news['url'] . '" class="news-img">
          <img src="' . SITE . '/uploads/news/' . $imagePrefix . $news['image_url'] . '" alt="' . $news['title'] . '" title="' . $news['title'] . '">          
          <span class="news-date">' . date('d.m.Y', strtotime($news['add_date'])) . '</span>
        </a>' : '';
                $html .= '

      <a  class="mc-n-c__card" href="' . SITE . '/news/' . $news['url'] . '">
        ' . $img . '
            <p>' . $news['title'] . '</p>
            <div  class="mc-n-c__card-ico-dat">
                <svg
                class="icon"
                width="16"
                height="14"
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                >
                <path
                    d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
                />
                </svg>
                <span>' . date('d.m.Y', strtotime($news['add_date'])) . '</span>
            </div>
        
      </a> ';
            }
        }

        $html .= '	</div>
	 
        </section>
    ';
        return $html;
    }

    /**
     * Запускает механизм вывода анонсов для новостей
     * @param type $count - количество выводимых анонсов
     */
    public static function runNews($count = 3)
    {
        $data = self::getListNews($count);

        $listNews = $data['listNews'];
        if (!empty($listNews)) {
            echo '<div class="max-cont-width"><div class="mc__news-cont">';
            foreach ($listNews as $news) {
                echo self::printAnonsNews($news);
            }
            if (defined('TEMPLATE_INHERIT_FROM') && (is_array($data['pagination']))) {
                echo component('pagination', $data['pagination']);
            }else{
                echo $data['pagination'];
            }
            echo '</div></div>';
        } else {
            echo "Пока новостей нет!";
        }
    }

    //Возвращает список новостей
    public static function getListNews($count = 100, $usepager = true)
    {
        $res = DB::query('SHOW TABLES LIKE "'.PREFIX.'mpl_news"');
        if (DB::numRows($res)) {
            if ($usepager) {
                //Получаем список новостей
                if (!empty($_GET["page"])) {
                    $page = $_GET["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
                } else {
                    $page = 1;
                }

                $navigator = new Navigator("SELECT  *  FROM `" . PREFIX . "mpl_news` WHERE `add_date` <= now() ORDER BY `add_date` DESC ", $page, $count); //определяем класс
                $news = $navigator->getRowsSql();
                $pagination = $navigator->getPager();
            } else {
                $navigator = new Navigator("SELECT  *  FROM `" . PREFIX . "mpl_news` WHERE `add_date` <= now() ORDER BY `add_date` DESC", 1, $count); //определяем класс
                $news = $navigator->getRowsSql();
                $pagination = '';
            }

            return array('listNews' => $news, 'pagination' => $pagination);
        }
        return false;
    }

// Возвращает данные о запрошенной новости.
    public static function getNewsByUrl($url)
    {
        $result = array();
        $res = DB::query('
            SELECT  *
            FROM `' . PREFIX . 'mpl_news`  
            WHERE url="' . DB::quote($url, true) . '.html" OR url="' . DB::quote($url, true) . '"'
        );
        if ($result = DB::fetchAssoc($res)) {
            return $result;
        }
        return $result;
    }

}
