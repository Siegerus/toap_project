<?php

/**
 * Класс Pactioner наследник стандарного Actioner
 * Предназначен для выполнения действий,  AJAX запросов плагина 
 *
 * @author Avdeev Mark <mark-avdeev@mail.ru>
 */
class Pactioner extends Actioner {

  private $pluginName = 'site-block-editor';
  
  private function addEntity($array) {    
    unset($array['id']);
    $result = array();
    DB::buildQuery('INSERT INTO `'.PREFIX.$this->pluginName.'` SET ', $array);
    return $result;
  }

  public function getPublicCode() {    
    if (USER::access('plugin') < 1) {
      $this->messageError = $this->lang['ACCESS_VIEW'];
      return false;
    }
    $arg['id'] = $_POST['id'];
    $this->data = SiteBlockEditor::getCode($arg); //Метод для шорткода
    return true;
  }

  /**
   * @deprecated Нигде не используется
   */
  private function updateEntity($array) {
    $id = $array['id'];
    $result = false;
    if (!empty($id)) {
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'`
        SET '.DB::buildPartQuery($array).'
        WHERE id = '.DB::quote($id))) {
        $result = true;
      }
    } else {
      $result = $this->addEntity($array);
    }
    return $result;
  }

  public function deleteEntity() {
    if (USER::access('plugin') < 2) {
      $this->messageError = $this->lang['ACCESS_EDIT'];
      return false;
    }
    $this->messageSucces = 'Шорткод удален';
    $this->messageError = 'Ошибка удаления!';
    if (DB::query('DELETE FROM `'.PREFIX.$this->pluginName.'` WHERE `id`= '.DB::quote($_POST['id']))) {
      return true;
    }
    return false;
  }

  public function getEntity() {   
    if (USER::access('plugin') < 1) {
      $this->messageError = $this->lang['ACCESS_VIEW'];
      return false;
    }
    $res = DB::query('
      SELECT * 
      FROM `'.PREFIX.$this->pluginName.'`
      WHERE `id` IN ('.DB::quoteIN($_POST['id']).') ORDER BY id ASC');

    if ($row = DB::fetchAssoc($res)) {
      $this->data = $row;
      $this->data['alt'] = htmlspecialchars_decode($this->data['alt']);
      $this->data['title'] = htmlspecialchars_decode($this->data['title']);
      return true;
    } else {
      return false;
    }

    return false;
  }

  public function getRows() {  
    if (USER::access('plugin') < 1) {
      $this->messageError = $this->lang['ACCESS_VIEW'];
      return false;
    } 
    $res = DB::query('SELECT * FROM `'.PREFIX.$this->pluginName.'` ORDER BY id ASC');

    while ($row = DB::fetchAssoc($res)) {
      $this->data[] = $row;
    }

    foreach ($this->data as &$itemS) {
      $itemS['content'] = substr(strip_tags($itemS['content']),0,200);
    }
    unset($itemS);

    return false;
  }

  /**
   * Сохраняет и обновляет параметры записи.
   * @return type
   */
  public function saveEntity() {
    if (USER::access('plugin') < 2) {
      $this->messageError = $this->lang['ACCESS_EDIT'];
      return false;
    }

    $this->messageSucces = "Сохранено";
    $this->messageError = "Ошибка сохранения";

    unset($_POST['pluginHandler']);

    $_POST['alt'] = htmlspecialchars($_POST['alt']);
    $_POST['title'] = htmlspecialchars($_POST['title']);
    $_POST['content'] = trim($_POST['content']);

    if (!empty($_POST['id'])) {  // если передан ID, то обновляем
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'`
        SET '.DB::buildPartQuery($_POST).'
        WHERE id = '.DB::quote($_POST['id']))) {
      } else {
        return false;
      }
    } else {
      // если  не передан ID, то создаем
      if (DB::buildQuery('INSERT INTO `'.PREFIX.$this->pluginName.'` SET ', $_POST)) {
        $_POST['id'] = DB::insertId();
        
        DB::query('
          UPDATE `'.PREFIX.$this->pluginName.'`  
          SET '.DB::buildPartQuery($_POST).'      
          WHERE `id` = '.DB::quote($_POST['id'])
        );
      } else {
        return false;
      }
    }
    return true;
  }
}