<?php
//die("update");
session_start();

define("DEBUG", 1);
define("DIR_APP", __DIR__ . "/../app");

ini_set('error_reporting', DEBUG ? E_ALL : "E_ALL~E_NOTICE");
ini_set("display_errors", DEBUG ? "On" : "Off");
ini_set("default_charset", "UTF-8");

if (DEBUG) {
    ini_set('log_errors', 'On');
    ini_set('error_log', '/Volumes/D/localhost/mp3fordjru/error.log');
}

setlocale(LC_ALL, 'en_US.UTF-8');

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

require_once(DIR_APP . "/define.php");
require_once(DIR_APP . "/class.Controller.php");
require_once(DIR_APP . "/class.Model.php");
require_once(DIR_APP . "/class.Load.php");
require_once(DIR_APP . "/helpers.php");
require_once(DIR_SMARTY . "/libs/SmartyBC.class.php");

// смарти лучше создать здесь и задать параметры. Это из-за аякса.
$smarty = new Smarty();
$smarty->setTemplateDir(DIR_VIEW);
$smarty->setCompileDir(DIR_SMARTY . '/templates_c');
$smarty->setCacheDir(DIR_SMARTY . '/cache');
$smarty->setConfigDir(DIR_SMARTY . '/configs');

// зарезирвируем переменную для PDO
$PDO = null;

// назначим автолоадер и зададим поиск по расширению. Автозагрузка будет происходить по контроллерам, моделям.
spl_autoload_extensions('.php');
spl_autoload_register('customAutoloader');

// обработаем url и возьмем названия контроллера, метода, параметров и шаблона. Переведем в нижний регистр намеренно.
$req_uri = mb_strtolower(URL);
$sUrl = substr_count($req_uri, "?") ? substr($req_uri, 0, strpos($req_uri, "?")) : $req_uri;
$aUrl = explode("/", $sUrl);

$preg_pattern_class = "/^[a-z][a-z0-9_]*$/"; // патерн для названия класса, их нужно разделить, т.к. у метода добавлено тире
$preg_pattern_method = "/^[a-z][-a-z0-9_]*$/"; // патерн для названия метода
$preg_ptrn_args = "/^[a-zа-я0-9][-a-zа-я0-9_.@]*$/ui"; // паттерн для аргументов метода, собака - для емэйлов
$preg_ptrn_tovar = "/^[a-z0-9][-a-z0-9.+]*_[0-9]{1,11}$/i"; // паттерн для карточки товара
$aDisallowedCtrlrs = ["controller", "download"]; // дополнительная блокировка на классы в пространстве "контроллеры"
$sControllerName = "";
$sControllerMethod = "";
$aArgs = []; // аргументы
$cache_id = substr(md5(URL), 0, 10);
$aOpenForCacheControllers = ["track", "top", "main", "genre", "info"];
array_shift($aUrl); // уберем первый пустой элемент

// если зашли в корень
if (empty($aUrl[0])) {
    $sControllerName = "main";
    $sControllerMethod = "index";

// если это карточка товара
} elseif (!empty($aUrl[0]) && preg_match($preg_ptrn_tovar, $aUrl[0])) {
    $sControllerName = "track";
    $sControllerMethod = "index";
    $aArgs[] = $aUrl[0];

// если это скачка
} elseif (!empty($aUrl[0]) && $aUrl[0] === "download" && !empty($aUrl[1]) && is_numeric($aUrl[1])) {
    $sControllerName = $aUrl[0];
    $sControllerMethod = "index";
    $aArgs[] = $aUrl[1];

// если это контроллер, но без метода
} elseif (!empty($aUrl[0]) && empty($aUrl[1]) && preg_match($preg_pattern_class, $aUrl[0]) && !in_array($aUrl[0], $aDisallowedCtrlrs) && $aUrl[0] !== "main") {
    $sControllerName = $aUrl[0];
    $sControllerMethod = "index";

// если это контроллер и с методом
} elseif (!empty($aUrl[0]) && !empty($aUrl[1]) && preg_match($preg_pattern_class, $aUrl[0]) && !in_array($aUrl[0], $aDisallowedCtrlrs) && $aUrl[1] !== "index" && preg_match($preg_pattern_method, $aUrl[1])) {
    $sControllerName = $aUrl[0];
    $sControllerMethod = substr_count($aUrl[1], "-") ? str_replace("-", "_", $aUrl[1]) : $aUrl[1]; // обработаем "-", на "_"

    // если есть еще параметры, то подъсоединим их как аргументы в ф-ию
    if (sizeof($aUrl) > 2) {
        for ($i = 2, $size = sizeof($aUrl); $i < $size; $i++) {
            if (isset($aUrl[$i]) && preg_match($preg_ptrn_args, $aUrl[$i])) {
                $aArgs[] = $aUrl[$i];
            }
        }
    }
}

$err = "";
// проверим наличие контроллера
if (empty($sControllerName)) {
    $err = "Err: controller is empty.";

} else {
    foreach (get_declared_classes() as $val) {
        if (strtolower($val) == strtolower("App\\Controller\\" . $sControllerName)) {
            $err = "Err: this class (" . $sControllerName . ") is exist.";
            break;
        }
    }

    if (empty($err) && !class_exists("App\\Controller\\" . $sControllerName)) {
        $err = "Err: not found class controller.";
    }
}

// проверим наличие метода
if (empty($err)) {
    if (empty($sControllerMethod)) {
        $err = "Err: method is empty.";

    } else {
        // исключение на Профиль, урлы все разрешены, кроме тех каторые начинаются на "ajax"
        if (($sControllerName === 'profile' || $sControllerName === 'admin') && substr($sControllerMethod, 0, 4) !== 'ajax') {
            $sControllerMethod = 'index';

        } else {
            if (!method_exists("App\\Controller\\" . $sControllerName, $sControllerMethod)) {
                $err = "Err: not found method in class";
            }
        }
    }
}

// если есть ошибки, то прекратим работу
if (!empty($err)) {
    !DEBUG ? die($err) : abort(404);
}

$tamplate_path = DIR_VIEW . "/" . $sControllerName . "/" . $sControllerMethod . ".tpl";

// тут проверка на кеш, в зависимости от контроллера
if (in_array($sControllerName, $aOpenForCacheControllers)) {
    $smarty->setCacheLifetime(CACHE_TIME);
    $smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
    //$smarty->setCompileCheck(false);
}

// если это не кеш
if (!$smarty->isCached($tamplate_path, $cache_id)) {
    // тут нужно проверить на remember_me
    if (!isset($_SESSION["user"]) && isset($_COOKIE[COOKIE_REMEMBER_ME])) {
        $ar = explode("|", $_COOKIE[COOKIE_REMEMBER_ME]);
        $regexp = '/^[a-z0-9]{16}$/ui';
        $email = (!empty($ar[0]) && filter_var($ar[0], FILTER_VALIDATE_EMAIL)) ? $ar[0] : "";
        $timestamp = (!empty($ar[1]) && filter_var($ar[1], FILTER_VALIDATE_INT)) ? abs(intval($ar[1])) : 0;
        $key = (!empty($ar[2]) && filter_var($ar[2], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $regexp]])) ? $ar[2] : "";
        $raznost = $timestamp - time();

        if (!empty($email) && $timestamp && !empty($key) && $raznost) {
            $Profile = App\Load::model('Profile');
            $Profile->checkRememberMe($email, $key);
        }
    }

    // вызовим метод у объекта (база и Смарти необходимы)
    $tmp = "App\\Controller\\" . $sControllerName; // именно так
    $output = call_user_func([new $tmp(), $sControllerMethod], $aArgs);

    // после выполнения метода класса, ставятся нужные заголовки.
    // определяем на ajax, xml
    // если аякс, не позволяем создавать кеш, т.е. туда не доходим
    foreach (headers_list() as $val) {
        if (stristr($val, 'Content-Type: application/json') || stristr($val, 'Content-Type: text/xml')) {
            die($output);
        }
    }

    // проверим на наличие шаблона (после аякса)
    if (!$smarty->templateExists($tamplate_path)) {
        die("Err: not found tamplate (" . $sControllerName . "/" . $sControllerMethod . ".tpl).");
    }

    // назначим переменные
    if (is_array($output) && sizeof($output)) {
        $smarty->assign($output);
    }
}

// сохраним на всякий случай до отображения шаблона
setcookie(COOKIE_LAST_VISIT, time(), time() + 3600 * 24 * 30);

// покажем отображение, внутри могут работать ф-ии семейства insert
$smarty->display($tamplate_path, $cache_id);

// если есть переменная $smarty.session.error_download
if (isset($_SESSION['error_download'])) {
    unset($_SESSION['error_download']);
}