<?php

/**
 * Базовая конфигурация
 */
// * Апи ключ вашего акканута
$apiKey = 'f98b13895cfd388ce39f7c86c1a98fba';
// * Домен проекта на который происходит отправка заказов
$domain = 'shakes.pro';
// Урл оригинального лендинга, необходим для корректого расчета Вашей статистики
$landingUrl = 'http://cod.wellcardsale.com';
// * Идентификатор оффера на который вы льете
$offerId = '4763';
// Код потока заведенного в системе, если указан, статистика будет записываться на данный поток
$streamCode = '';
// Страница, отдаваемая при успешном заказе
$successPage = 'success.html';
// Страница, отдаваемая в случае ошибки
$errorPage = 'index.html';
/**
 * Формирование отправляемого заказа
 */
$url = "http://$domain?r=/api/order/in&key=$apiKey";
$order = [
    'countryCode' => (!empty($_POST['country']) ? $_POST['country'] : ($_GET['country'] ? $_GET['country'] : 'RU')),
    'createdAt' => date('Y-m-d H:i:s'),
    'ip' => (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null), // ip пользователя
    'landingUrl' => $landingUrl,
    'name' => (!empty($_POST['name']) ? $_POST['name'] : ($_GET['name'] ? $_GET['name'] : '')),
    'offerId' => $offerId,
    'phone' => (!empty($_POST['phone']) ? $_POST['phone'] : ($_GET['phone'] ? $_GET['phone'] : '')),
    'referrer' => (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
    'streamCode' => $streamCode,
    'sub1' => (!empty($_POST['sub1']) ? $_POST['sub1'] : ''),
    'sub2' => (!empty($_POST['sub2']) ? $_POST['sub2'] : ''),
    'sub3' => (!empty($_GET['sub3']) ? $_GET['sub3'] : ''),
    'sub4' => (!empty($_GET['sub4']) ? $_GET['sub4'] : ''),
    'userAgent' => (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-'),
];

/**
 * Отправка заказа
 */
/**
 * @see http://php.net/manual/ru/book.curl.php
 */
$curl = curl_init();
/**
 * @see http://php.net/manual/ru/function.curl-setopt.php
 */
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $order);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
/**
 * @see http://php.net/manual/ru/language.exceptions.php
 */
try {
    $responseBody = curl_exec($curl);
    // тело оказалось пустым
    if (empty($responseBody)) {
        throw new Exception('Error: Empty response for order. ' . var_export($order, true));
    }
    /**
     * @var StdClass $response
     */
    $response = json_decode($responseBody, true);
    // возможно пришел некорректный формат
    if (empty($response)) {
        throw new Exception('Error: Broken json format for order. ' . PHP_EOL . var_export($order, true));
    }
    // заказ не принят API
    if ($response['status'] != 'ok') {
        throw new Exception('Success: Order is accepted. '
            . PHP_EOL . 'Order: ' . var_export($order, true)
            . PHP_EOL . 'Response: ' . var_export($response, true)
        );
    }
    /**
     * логируем данные об обработке заказа
     * @see http://php.net/manual/ru/function.file-put-contents.php
     */
    @file_put_contents(
        __DIR__ . '/order.success.log',
        date('Y.m.d H:i:s') . ' ' . $responseBody,
        FILE_APPEND
    );
    curl_close($curl);

    if(!empty($successPage) && is_file(__DIR__ . '/' . $successPage)) {
        include __DIR__ . '/' . $successPage;
    }
} catch (Exception $e) {
    /**
     * логируем ошибку
     * @see http://php.net/manual/ru/function.file-put-contents.php
     */
    @file_put_contents(
        __DIR__ . '/order.error.log',
        date('Y.m.d H:i:s') . ' ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
        FILE_APPEND
    );

    if(!empty($errorPage) && is_file(__DIR__ . '/' . $errorPage)) {
        include __DIR__ . '/' . $errorPage;
    }
}


// config cpabazuka
$offer_id = '9'; //offer_id
$user_id = '2'; //user_id
$url = 'https://cpabazuka.scaletrk.com/click?o='.$offer_id.'&a='.$user_id.'&extra_1='.$_POST["name"].'&extra_2='.$_POST["phone"].'';
$headers = get_headers($url, 1);
$last_urls_array = $headers['Location'];
$cpabazuka = str_replace('https://ya.ru?click_id=', '', $last_urls_array);

//make pending
$url = 'https://cpabazuka.scaletrk.com/track/goal-by-click-id?click_id='.$cpabazuka;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
echo curl_exec($ch);

?>
