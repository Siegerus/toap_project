<?php

/**
 * Модель: Registration
 *
 * Класс Models_Registration реализует логику регистрации новых пользователей.
 * - Проверяет корректность введенных данных в форме регистрации;
 * - Регистрирует нового пользователя, заносит данные в базу сайта;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Registration {

  /**
   * Проверяет корректность введенных данных в форме регистрации.
   * <code>
   *   $userData = array(
   *     'email' => 'test@mail.ru',
   *     'pass' => '123456',
   *   );
   *   $model = new Models_Registration();
   *   $res = $model->validDataForm($userData);
   * </code>
   * @param array $userData массив данных пользователя.
   * @param string $mode режим проверки данных (full|pass) полный (по умолчанию) или только пароль.
   * @return string ошибка в случае не верного ввода данных в одном из полей.
   */
  public function validDataForm($userData, $mode = 'full') {
    $error = '';
    // Проверка электронного адреса.
    // если при регистрации в поле емаил был указан номер телефона, то система распознаент и отработает корректно
    // это позволит всем текущим клиентам могуты без труда переадаптировать шаблон для смс(телефон) авторизации
    if ('full' == $mode || 'nopass' == $mode ){
            $userDataEmail = $userData['email'];

        if (USER::getUserInfoByEmail($userDataEmail,'login_email') && !empty($userDataEmail)) {
            // $error .= '<span class="email-in-use">Указанный email уже используется</span>';
            $error .= '<span class="email-in-use">'.MG::restoreMsg('msg__reg_email_in_use').'</span>';
        }
    }
    if ('nopass' != $mode) {
        // Пароль должен быть больше 5-ти символов.
        if (strlen($userData['pass']) < 5) {
          // $error .= '<span class="passError">Пароль менее 5 символов</span>';
          $error .= '<span class="passError">'.MG::restoreMsg('msg__reg_short_pass').'</span>';
        }
        // Проверяем равенство введенных паролей.
        if (URL::getQueryParametr('pass2') != $userData['pass']) {
          // $error .= '<span class="wrong-pass">Введенные пароли не совпадают</span>';
          $error .= '<span class="wrong-pass">'.MG::restoreMsg('msg__reg_wrong_pass').'</span>';
        }
    }
    
    if ('full' == $mode || 'nopass' == $mode ){

      // Проверка электронного адреса. 
      if (!MG::checkEmail($userDataEmail)) {
          $msg = 'msg__reg_wrong_email';
                $error .= '<span class="errorEmail">'.MG::restoreMsg($msg).'</span>';
      }

      $mailsBlackListSetting = MG::getSetting('mailsBlackList');
      $mailsBlackList = explode(' ', trim($mailsBlackListSetting));

      if ($mailsBlackList) {
        foreach ($mailsBlackList as $forbiddenMail) {
          if (!$forbiddenMail) {
            continue;
          }
          if (substr($userDataEmail, -strlen($forbiddenMail)) === $forbiddenMail) {
            $error .= '<span class="errorEmail">'.MG::restoreMsg('msg__reg_blocked_email').'</span>';
          }
        }
      }
      
      if(MG::getSetting('useCaptcha')=="true" && MG::getSetting('useReCaptcha') != 'true' ){ 
        if (strtolower(URL::getQueryParametr('capcha')) != strtolower($_SESSION['capcha']) || empty($_SESSION['capcha'])) {
          $error .= "<span class='error-captcha-text'>".MG::restoreMsg('msg__captcha_incorrect')."</span>";
        }
      }
      if (!MG::checkReCaptcha()) {
        $error .= "<span class='error-captcha-text'>".MG::restoreMsg('msg__recaptcha_incorrect')."</span>";
      }
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $error, $args);
  }

}