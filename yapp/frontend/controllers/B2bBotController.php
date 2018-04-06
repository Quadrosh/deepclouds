<?php

namespace frontend\controllers;


use common\models\B2bBotRequest;
use common\models\B2bBotUser;
use common\models\B2bDealer;
use common\models\B2bSender;
use common\models\BotSettings;
use yii\filters\ContentNegotiator;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;
use yii\web\Response;


class B2bBotController extends \yii\web\Controller
{
    /**
     * @var B2bBotUser
     */
    private $user;

    /**
     * @var B2bDealer
     */
    private $dealer;

    /**
     * @var B2bBotRequest
     */
    private $request;

    /**
     * @var array
     */
    private $settings;

    public function behaviors() {
        return [
            'contentNegotiator' => [
                'class'   => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
//            'rateLimiter'       => [
//                'class' => RateLimiter::className(),
//            ],
//            'authenticator' => [
//                'class' => \app\components\auth\QueryParamAuth::className(),
////                'except' => [ 'create' ],
//            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['do','test'])) {
            $this->enableCsrfValidation = false;
        }

        $this->settings = BotSettings::find()
            ->where(['bot_name'=>'b2b'])
            ->indexBy('name')
            ->asArray()
            ->all();

        return parent::beforeAction($action);
    }


    /*
     * Основной метод, принимает запросы от пользователя.
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     *    ['message' => 'ok', 'code' => 200]
     * */
    public function actionDo()
    {

        $input = Yii::$app->request->getRawBody();
        $updateId = Yii::$app->request->post('update_id');
        $message = Yii::$app->request->post('message'); // array
        $callbackQuery = Yii::$app->request->post('callback_query'); // array
        $inlineQuery = Yii::$app->request->post('inline_query'); // array

        $cleanInput = Json::decode($input);
        $allreadyRequested = B2bBotRequest::find()->where(['update_id'=>$cleanInput['update_id']])->one();

        if ($allreadyRequested) {
            return 'this request in process';
        }

        Yii::info([
            'action'=>'request from User',
            'input'=>Json::decode($input),

        ], 'b2bBot');



        if ($message) {
            $user = B2bBotUser::find()->where(['telegram_user_id'=>$message['from']['id']])->one();
        } elseif ($inlineQuery){
            $user = B2bBotUser::find()->where(['telegram_user_id'=>$inlineQuery['from']['id']])->one();
        } elseif ($callbackQuery){
            $user = B2bBotUser::find()->where(['telegram_user_id'=>$callbackQuery['from']['id']])->one();
        } else {
            $user = null;
        }




        if (!$user) {
            $user = new B2bBotUser;
            $user['telegram_user_id'] = $message['from']['id'];
            $user['first_name'] = isset($message['from']['first_name'])?$message['from']['first_name']: '---';
            $user['last_name'] = isset($message['from']['last_name'])?$message['from']['last_name']: '---';
            $user['username'] = isset($message['from']['username'])?$message['from']['username']: '---';
            $user['status'] = 'unconfirmed';
            $user->save();
            if( !$user->save()){
                Yii::info([
                    'action'=>'Error when save user',
                    'errors'=>$user->errors,
                ], 'b2bBot');
            }
        }



        $this->user = $user;
        $this->dealer = $user->dealer;


        // request save
        $this->request = new B2bBotRequest;
        $this->request['user_id'] = $this->user['id'];
        $this->request['update_id'] = strval($updateId);
        $this->request['user_time'] = intval($message['date']);
        $this->request['status'] = 'received';




        if ($message) {

            if (isset($message['text'])) {
                $this->request['request'] = $message['text'];
            }
            elseif (isset($message['contact'])){

                $this->request['request'] = 'phone/'.$message['contact']['phone_number'];

                Yii::info([
                    'action'=>'request',
                    '$this->request'=> $this->request['request'],
                ], 'b2bBot');
            }
            else {
                $this->request['request'] = 'no text';
            }

        } elseif ($inlineQuery){
            $this->request['request'] = 'inlineQuery '.$inlineQuery['query'];
        } elseif ($callbackQuery){
            $this->request['request'] = 'callbackQuery '.$callbackQuery['data'];
        }

        $this->request->save();




        //  проверка авторизации
        if (!$this->checkAuth()) {
            return ['message' => 'ok', 'code' => 200];
        }


        if ($inlineQuery) {
          return $this->inlineQueryAction($inlineQuery);
        }

        if ($message) {
            return $this->textMessageAction($message);
        }

        if ($callbackQuery) {
            return $this->callBackQueryAction($callbackQuery);
        }

    }


    /*
     * проверка авторизации
     * при первом обращении пользователя проходит
     * процесс уточнения контактных данных и валидности пользователя
     *
     *
     * @return bool Возвращает true при успешной авторизации
     * */
    private function checkAuth()
    {
        if ($this->user['status'] == 'active' ){
            return true;
        }

        if ( $this->user['status'] == 'unconfirmed' ) {
            $this->sendMessage([

                'chat_id' => $this->user['telegram_user_id'],
                'text' => $this->settings['m_start_authorize']['value'],
                'reply_markup' => Json::encode([
                    'one_time_keyboard'=> true,
                    'keyboard'=>[
                        [
                            ['text'=>'Отправить номер', 'request_contact'=> true],
                        ],
                    ]
                ]),
            ]);
            $this->user['status'] = 'user_phone_requested';
            $this->user->save();
            return false ;
        }

        if ( $this->user['status'] == 'user_phone_requested' || $this->user['status'] == 'doubled_entry') {

            if (substr($this->request['request'],0,6) == 'phone/' ){
                $commandArr = explode('/', $this->request['request']);
                $phone = $commandArr[1];

                if (substr($phone,0,1) == '+') {
                    $count = strlen($phone)-1;
                    $phone = substr($phone,1,$count);
                }

                $this->user['phone'] = $phone;
                $this->user->save();
                Yii::info([
                    'action'=>'user_phone_saved',
                    'updateId'=>$this->request['update_id'],
                    'user phone'=>$this->request['request'],
                ], 'b2bBot');

                $dealer = B2bDealer::find()->where(['like', 'entry_phones', $this->user['phone']])->one();

                if ($dealer != null) { // есть дилер у кого есть этот номер в доступах
                    if (count(B2bDealer::find()->where(['like', 'entry_phones', $this->user['phone']])->all())>1) {
                        Yii::info([
                            'action'=>'founded more than 1 dealer with user phone in entry_phones',
                            'updateId'=>$this->request['update_id'],
                            'dealers'=>B2bDealer::find()->where(['like', 'entry_phones', $this->user['phone']])->all(),
                            'phone'=>$this->user['phone'],
                        ], 'b2bBot');
                        $this->user['status'] = 'doubled_entry';
                        $this->user['b2b_dealer_id']= $this->dealer['id'];
                        $this->user->save();
                        $this->sendMessage([
                            'chat_id' => $this->user['telegram_user_id'],
                            'text' => $this->settings['m_authorize_doubled']['value'],
                        ]);
                        return false;
                    }

                    Yii::info([
                        'action'=>'founded dealer with user phone in entry_phones',
                        'updateId'=>$this->request['update_id'],
                        'dealer'=>$dealer,
                        'phone'=>$this->user['phone'],
                    ], 'b2bBot');

                    $this->dealer = $dealer;

                    if ($this->dealer['status'] != 'active') { // неактивный дилер
                        $this->sendMessage([
                            'chat_id' => $this->user['telegram_user_id'],
                            'text' => $this->settings['m_authorize_inactive_dealer']['value'],
                        ]);
                        return false;
                    } else { // дилер с активным статусом
                        $this->user['status'] = 'active';
                        $this->user['b2b_dealer_id']= $this->dealer['id'];
                        $this->user->save();
                        $this->sendMessage([
                            'chat_id' => $this->user['telegram_user_id'],
                            'text' => $this->settings['m_authorize_success']['value'],
                        ]);
                        $this->options();
                        return false;
                    }

                } else {  // нет дилера у кого этот номер в доступах
                    $this->sendMessage([
                        'chat_id' => $this->user['telegram_user_id'],
                        'text' => $this->settings['m_authorize_fault']['value'],
                    ]);
                    return false ;
                }


            } else {
                $this->sendMessage([
                    'chat_id' => $this->user['telegram_user_id'],
                    'text' => $this->settings['m_authorize_push_the_button']['value'],
                    'reply_markup' => Json::encode([
                        'one_time_keyboard'=> true,
                        'keyboard'=>[
                            [
                                ['text'=>'Отправить номер', 'request_contact'=> true],
                            ],
                        ]
                    ]),
                ]);
            }
        }



    }


    /*
     * Отмена авторизации и привязки пользователя к дилеру
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function unAuthorise(){
        $this->user['status'] = 'unconfirmed';
        $this->user['b2b_dealer_id'] = null;
        $this->user->save();
        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * Обработка входящего текстового сообщения
     *
     * @var array $message
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function textMessageAction($message){


        if ($this->user['bot_command'] == 'sendEmail'){

            return $this->emailProcess($message['text']);
        }

        if (trim(strtolower($message['text'])) == '/start') {
            return $this->helloMessage();
        }

        if (trim(strtolower($message['text'])) == '/orders' ||
            $message['text'] == 'Мои заказы') {
            return $this->orders();
        }

        elseif (substr($message['text'],0,6) == 'order/' ){

            $commandArr = explode('/', $message['text']);
            $orderId = $commandArr[1];
            return $this->order($orderId);
        }

        elseif (strtolower($message['text']) == '/options' ||
            $message['text'] == 'Опции'){
            return $this->options();
        }

        elseif (trim(strtolower($message['text'])) == '/help' ||
            $message['text'] == 'Помощь') {
            return $this->help();
        }


        // отмена авторизации
        elseif (trim(strtolower($message['text'])) == '/unauthorize' ){
            return $this->unAuthorise();
        }

        // инфо по товару в один запрос
        elseif (substr($message['text'],0,8) == 'product/' ||
            substr($message['text'],0,6) == 'товар/'){

            $commandArr = explode('/', $message['text']);
            $productId = $commandArr[1];

            return $this->oneProductProcess($productId);
        }


        // сообщение менеджеру - инициализация
        elseif (trim(strtolower($message['text'])) == '/email' ||
            $message['text'] == 'Сообщение менеджеру' ||
            $this->user['bot_command'] == 'first_name_request' ||
            $this->user['bot_command'] == 'last_name_request') {
            return $this->emailInit();
        }


        // Инфо по артикулу - инициализация
        elseif (trim(strtolower($message['text'])) == '/product' || $message['text'] == 'Инфо по артикулу' ){
            return $this->oneProductInit();
        }
        // Инфо по артикулу - обработка запроса
        elseif ($this->user['bot_command'] == 'oneProductInfo'){
            return $this->oneProductProcess($message['text']);
        }

        // поиск - инициализация
        elseif (trim(strtolower($message['text'])) == '/search' || $message['text'] == 'Поиск товара' ){
            return $this->searchInit();
        }
        elseif ($message['text'] == '/search_20'){
            return $this->searchInit(20);
        }
        elseif ($message['text'] == '/search_30'){
            return $this->searchInit(30);
        }
        // поиск - обработка запроса
        elseif ($this->user['bot_command'] == 'search'){
            return $this->searchProcess($message['text']);
        }


        // обработка запроса недоступных значений лимита поиска
        elseif (substr($this->user['bot_command'],0,7) == 'search_'){
            $commandArr = explode('_', $this->user['bot_command']);
            $limit = $commandArr[1];
            if ($limit > 30) {
                $this->sendMessage([
                    'chat_id' => $message['from']['id'],
                    'text' => $this->settings['m_search_more_than_alowed']['value'],
                ]);
                return ['message' => 'ok', 'code' => 200];
            }
            return $this->searchProcess($message['text'], $limit);
        }


        elseif (trim(strtolower($message['text'])) == '/debug' ){

           return $this->debug();
        }



        $this->sendMessage([
            'chat_id' => $message['from']['id'],
            'text' =>  $this->settings['m_no_such_command']['value'],
        ]);
        return $this->options();

    }


    /*
     * Обработка входящего сообщения типа Callback Query
     * при старте обработки отправляет сообщение типа answerCallbackQuery
     * (индикация что запрос принят и обрабатывается)
     * обрабатывает поле 'data'
     *
     * @var array $callbackQuery
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function callbackQueryAction($callbackQuery)
    {
        $this->answerCallbackQuery([
            'callback_query_id' => $callbackQuery['id'],
            'text' =>  $this->settings['m_answer_callback']['value'],
        ]);
        Yii::info([
            'action'=>'request Callback Query',
            'updateId'=>$this->request['update_id'],
            'callbackQuery'=>$callbackQuery,
        ], 'b2bBot');

        if ($callbackQuery['data'] == '/orders') {
            return $this->orders();
        }
        elseif ($callbackQuery['data'] == '/options') {
            return $this->options();
        }

        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * Обработка входящего сообщения типа Inline Query
     *
     * Отправляет пользователю массив результатов
     * пользователь получает результаты в виде всплывающего списка и выбирает один из них.
     * Боту отправляется текстовое сообщение поля input_message_content -> message_text
     *
     * @var array $inlineQuery
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function inlineQueryAction($inlineQuery)
    {
        Yii::info([
            'action'=>'request Inline Query',
            'updateId'=>$this->request['update_id'],
            'inlineQuery'=>$inlineQuery,
        ], 'b2bBot');

//           список заказов
        if ($inlineQuery['query'] == '/order_details') {
            $serverResponse = $this->getOrdersFromServer([
                'phone' => $this->dealer['phone'],
            ]);

            Yii::info([
                'action'=>'response from Server for Inline Query',
                'updateId'=>$this->request['update_id'],
                '$inlineQueryId'=>$inlineQuery['id'],
                'serverResponse'=>$serverResponse,
            ], 'b2bBot');

            if (isset($serverResponse['error'])) {
                return $this->sendErrorInline($serverResponse['message'],$inlineQuery['id']);
            }

            $results = [];
            foreach ($serverResponse as $order) {
                $results[] = [
                    'type' => 'article',
                    'id' => $order['orderId'],
                    'title' =>
                        $order['orderId'].', '.$order['totalItems'].'поз., '.$order['totalCost'].'р.',
                    'description' =>
                        'Доставка - '.$order['deliveryType']
                        .' / '.$order['status']['status']
                        .' / '.$order['status']['payment']
                        .' / '.$order['status']['delivey'],
                    'input_message_content'=>[
                        'message_text'=> 'order/' . $order['orderId'],
                        'parse_mode'=> 'html',
                        'disable_web_page_preview'=> true,
                    ],
                ];
            };
            $this->answerInlineQuery([
                'inline_query_id' => $inlineQuery['id'],
                'is_personal' => true,
                'results'=> Json::encode($results)
            ]);
        }

        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * Отправка пользователю опций
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function options()
    {
        $this->sendMessage([

            'chat_id' => $this->user['telegram_user_id'],
            'text' => $this->settings['m_options']['value'],
            'reply_markup' => Json::encode([
                'one_time_keyboard'=> true,
                'keyboard'=>[
                    [
                        ['text'=>'Инфо по артикулу'],
                        ['text'=>'Поиск товара']
                    ],
                    [
                        ['text'=>'Сообщение менеджеру'],
                        ['text'=>'Помощь'],
                    ],
                    [
                        ['text'=>'Мои заказы'],
                    ],
                ]
            ]),

        ]);
        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * Отправка пользователю памятки помощи
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function help(){
        $text = $this->settings['m_help_text']['value'].PHP_EOL;
        $this->sendMessage([

            'chat_id' => $this->user['telegram_user_id'],
            'text' => $text,
            'reply_markup' => Json::encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>'Опции', 'callback_data'=> '/options'],
                    ],

                ]
            ]),

        ]);
        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * Отправка пользователю приветственного сообщения
     *
     * @return boolean $this->checkAuth()
     * */
    private function helloMessage(){

        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $this->settings['m_hello']['value'],
        ]);
        return $this->checkAuth();
    }


    /*
     * Инициализация отправки сообщения менеджеру
     * изменяет поле 'bot_command' пользователя
     * при незаполненнх полях 'real_first_name' и 'real_last_name' запрашивает у пользователя имя/фамилию и сохраняет
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function emailInit(){

        // если пустые поля Имя Фамилия (real_first_name / last_name_request)
        if (!$this->user['real_first_name']) {
            if ($this->user['bot_command'] == 'first_name_request') {
                $this->user['real_first_name'] = $this->request['request'];
                $this->user['bot_command'] = 'last_name_request';
                $this->user->save();
                $this->sendMessage([
                    'chat_id' => $this->user['telegram_user_id'],
                    'text' => $this->settings['m_write_your_last_name']['value'],
                ]);
                return ['message' => 'ok', 'code' => 200];
            } else {
                $this->user['bot_command'] = 'first_name_request';
                $this->user->save();
                $this->sendMessage([
                    'chat_id' => $this->user['telegram_user_id'],
                    'text' => $this->settings['m_write_your_first_name']['value'],
                ]);
                return ['message' => 'ok', 'code' => 200];
            }
        }

        if ($this->user['bot_command'] == 'last_name_request') {
            $this->user['real_last_name'] = $this->request['request'];
            $this->user->save();
        }

        $this->user['bot_command'] = 'sendEmail';
        $this->user->save();


        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $this->settings['m_send_message_to_manager']['value'],
        ]);
        return ['message' => 'ok', 'code' => 200];
    }


    /*
     * отправка сообщения в b2b отдел
     *
     * @var string $text
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function emailProcess($text){

        $this->user['bot_command'] = null;
        $this->user->save();

        if ($this->dealer->sendEmail($text, $this->user['real_first_name'].' '.$this->user['real_last_name'])) {
            $this->sendMessage([
                'chat_id' => $this->user['telegram_user_id'],
                'text' => $this->settings['m_send_message_sent']['value'],
            ]);
            return ['message' => 'ok', 'code' => 200];
        } else {
            $this->sendMessage([
                'chat_id' => $this->user['telegram_user_id'],
                'text' => $this->settings['m_send_message_error']['value'],
            ]);
            return ['message' => 'ok', 'code' => 200];
        }
    }

    /*
     * Инициализация запроса в базе по одному артикулу
     * изменяет поле 'bot_command' пользователя
     * отправляет пользователю запрос на ввод артикула
     *
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function oneProductInit(){

        $this->user['bot_command'] = 'oneProductInfo';
        $this->user->save();

        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $this->settings['m_one_product_init']['value'],
        ]);
        return ['message' => 'ok', 'code' => 200];
    }

    /*
     * обработка запроса по одному артикулу
     * запрашивает у сервера информацию
     * отправляет результат пользователю
     *
     * @var string $query  Артикул
     * @return array Массив с кодом 200 (индикация успешной обработки запроса)
     * */
    private function oneProductProcess($query)
    {
        $this->user['bot_command'] = null;
        $this->user->save();
        $serverResponse = $this->getOneProductFromServer([
            'phone' => $this->dealer['phone'],
            'productCode' => $query
        ]);
        Yii::info([
            'action'=>'response from Server - one product info',
            'updateId'=>$this->request['update_id'],
            'serverResponse'=>$serverResponse,
        ], 'b2bBot');

        if (isset($serverResponse['error'])) {
            return $this->sendErrorMessage('Ошибка - '.$serverResponse['message']);
        }

        $responseToUser = '';
        mb_internal_encoding('utf-8');
        if (mb_strlen($serverResponse['description']) >3000) {
            $serverResponse['description'] = mb_substr($serverResponse['description'], 0, 3000).'...';
        }
        $responseToUser .= $serverResponse['productCode']
            .' '.$serverResponse['model']
            .PHP_EOL .' '.$serverResponse['description']
            .PHP_EOL
            .'Цена '.$serverResponse['retailPrice']
            .' / '.$serverResponse['personalPrice'].', '
            .'наличие ' .$serverResponse['quantity']['stock'].', '
            .'в пути ' .$serverResponse['quantity']['inroute']
            .PHP_EOL .'-------------------------'.PHP_EOL;


        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $responseToUser,
        ]);

        return ['message' => 'ok', 'code' => 200];
    }


    private function searchInit($limit = 10){
        $text = $this->settings['m_search_init_1']['value'];
        if ($limit != 10) {
            $text = $this->settings['m_search_not_10_init_1']['value'].$limit.$this->settings['m_search_not_10_init_2']['value'];
            $this->user['bot_command'] = 'search_'.$limit;
        } else {
            $this->user['bot_command'] = 'search';
        }
        $this->user->save();


        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $text.PHP_EOL.PHP_EOL.$this->settings['m_search_init_2']['value'],
        ]);
        return ['message' => 'ok', 'code' => 200];
    }


    private function searchProcess($query, $limit = 10)
    {
        $this->user['bot_command'] = null;
        $this->user->save();
        $serverResponseArr = $this->getSearchResultsFromServer([
            'phone' => $this->dealer['phone'],
            'query' => $query,
            'limit' => $limit,
        ]);
        Yii::info([
            'action'=>'response from Server - search',
            'updateId'=>$this->request['update_id'],
            'serverResponse'=>$serverResponseArr,
        ], 'b2bBot');
        if (isset($serverResponseArr['error'])) {
            return $this->sendErrorMessage($this->settings['m_search_api_error_message']['value'].$serverResponseArr['message']);
        }
        if ($serverResponseArr == []) {
            return $this->sendErrorMessage($this->settings['m_search_api_return_empty_array']['value']);
        }

        $responseToUser = '';
        mb_internal_encoding('utf-8');
        $iter = 0;
        foreach ($serverResponseArr as $item) {
            if (mb_strlen($item['description']) > 200) {
                $item['description'] = mb_substr($item['description'], 0, 200).'...';
            }
            $responseToUser .= $item['productCode']
                .' '.$item['model']
                .PHP_EOL .' '.$item['description']
                .PHP_EOL
                .'Цена '.$item['retailPrice']
                .' / '.$item['personalPrice'].', '
                .'наличие ' .$item['quantity']['stock'].', '
                .'в пути ' .$item['quantity']['inroute']
                .PHP_EOL .'-------------------------'.PHP_EOL;
            $iter++;
            if (count($serverResponseArr)>10 && $iter == 10) {
                $this->sendMessage([
                    'chat_id' => $this->user['telegram_user_id'],
                    'text' => $responseToUser,
                ]);
                $responseToUser='';
                $iter = 0;
            }

        }


        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $responseToUser,
        ]);

        return ['message' => 'ok', 'code' => 200];
    }


    private function order($orderId)
    {
        $serverResponse = $this->getOrderFromServer([
            'phone' => $this->dealer['phone'],
            'orderId' => $orderId,
        ]);
        Yii::info([
            'action'=>'response from Server - order',
            'updateId'=>$this->request['update_id'],
            'serverResponse'=>$serverResponse,
        ], 'b2bBot');

        if (isset($serverResponse['error'])) {
            return $this->sendErrorMessage($this->settings['m_order_api_error_message']['value'].$serverResponse['message']);
        }
        if ($serverResponse == []) {
            return $this->sendErrorMessage($this->settings['m_order_api_return_empty_array']['value']);
        }

        $responseToUser = $orderId.' - '
            .$serverResponse['totalCost'].'р.'
            .PHP_EOL
            .$serverResponse['status']['status'].' | '
            .$serverResponse['status']['payment'].' | '
            .$serverResponse['status']['delivey'].'  '
            .PHP_EOL
//            .'-------------------------'
            .PHP_EOL;
        foreach ($serverResponse['items'] as $item) {
            $responseToUser .= $item['productCode']
                .' '.$item['productName']
                .PHP_EOL
                .'заказ: '.$item['quantity'].', '
                .'резерв: '.$item['availability'].', '
                .'цена: ' .$item['price'].'р.'
                .PHP_EOL .'-------------------------'.PHP_EOL;
        }

        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $responseToUser,
            'reply_markup' => Json::encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>'Мои заказы', 'callback_data'=> '/orders'],
                        ['text'=>'Опции', 'callback_data'=> '/options'],
                    ],
                ]
            ]),
        ]);

        return ['message' => 'ok', 'code' => 200];
    }


    private function orders()
    {
        $orders = $this->getOrdersFromServer([
            'phone' => $this->dealer['phone'],
        ]);

        Yii::info([
            'action'=>'response from Server - orders',
            'updateId'=>$this->request['update_id'],
            'serverResponse'=>$orders,
        ], 'b2bBot');

        if (isset($orders['error'])) {
            return $this->sendErrorMessage('Ошибка - '.$orders['message']);
        }

        $responseToUser = '';

        foreach ($orders as $item) {
            $responseToUser .= $item['orderId']
                .' - '.$item['totalCost'].'р.'
                .PHP_EOL
                .$item['status']['status'].' | '
                .$item['status']['payment'].' | '
                .$item['status']['delivey']
                .PHP_EOL
//                .'-------------------------'
                .PHP_EOL;
        }




        $this->sendMessage([

            'chat_id' => $this->user['telegram_user_id'],
            'text' => $responseToUser,
            'reply_markup' => Json::encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>'Подробнее о заказе','switch_inline_query_current_chat'=> '/order_details'],
                        ['text'=>'Опции', 'callback_data'=> '/options'],
                    ],
                ]
            ]),
        ]);
        return ['message' => 'ok', 'code' => 200];
    }




    private function getOneProductFromServer($options = [])
    {
        $sender = new B2bSender;
        $jsonResponse = $sender->sendToServer(Yii::$app->params['b2bServerPathProdProduct'], $options);
        return Json::decode($jsonResponse);
    }


    private function getSearchResultsFromServer($options = [])
    {
        $sender = new B2bSender;
        $jsonResponse = $sender->sendToServer(Yii::$app->params['b2bServerPathProdProducts'], $options);
        return Json::decode($jsonResponse);
    }


    private function getOrderFromServer($options = [])
    {
        $sender = new B2bSender;
        $jsonResponse = $sender->sendToServer(Yii::$app->params['b2bServerPathProdOrder'], $options);
        return Json::decode($jsonResponse);
    }


    private function getOrdersFromServer($options = [])
    {
        $sender = new B2bSender;
        $jsonResponse = $sender->sendToServer(Yii::$app->params['b2bServerPathProdLastOrders'], $options);
        return Json::decode($jsonResponse);
    }




    /**
     *   @var array
     *   $this->answerCallbackQuery([
     *       'callback_query_id' => '3343545121', //require
     *       'text' => 'text', //Optional
     *       'show_alert' => 'my alert',  //Optional
     *   ]);
     *   The answer will be displayed to the user as a notification at the top of the chat screen or as an alert.
     *  On success, True is returned.
     */
    public function answerCallbackQuery(array $options = [])
    {
        $sender = new B2bSender;
        $jsonResponse = $sender->sendJob(
            $this->request['id'],
            'https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'] .
            '/answerCallbackQuery', $options);
        return $jsonResponse;
    }


    /**
     *   @var array
     *   sample
     *   $this->answerInlineQuery([
     *       'inline_query_id' => Integer,
     *       'user' => User, //Optional
     *   ]);
     *
     */
    public function answerInlineQuery(array $options = [])
    {
        $this->request['answer'] = 'inline_data';
        $this->request['status'] = 'processed';
        $this->request->save();
        $sender = new B2bSender;
        $jsonResponse = $sender->sendJob(
            $this->request['id'],
            'https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'] .
            '/answerInlineQuery', $options, true);
        return $jsonResponse;
    }




    /**
     *   @var array
     *   аргументы
     *  array  $options массив опций
     *  boolean  $dataInBody флаг отправки информации в теле запроса (кнопы )
     *
     */
    public function sendMessage($options, $dataInBody = true)
    {
        $this->request['answer'] = $options['text'];
        $this->request['status'] = 'processed';
        $this->request->save();
        $chat_id = $options['chat_id'];
        $urlEncodedText = urlencode($options['text']);
        $sender = new B2bSender;
        $jsonResponse = $sender->sendJob(
            $this->request['id'],
            'https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'].
            '/sendMessage?chat_id='.$chat_id .
            '&text='.$urlEncodedText, $options, $dataInBody
        );
        return $jsonResponse;
    }




    private function sendErrorMessage ($error){
        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => $error,
            'reply_markup' => Json::encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>'Опции', 'callback_data'=> '/options'],
                    ],
                ]
            ]),
        ]);
        return ['message' => 'ok', 'code' => 200];
    }

    private function sendErrorInline($error, $inlineQueryId){
        $result = [];
        $result[] = [
            'type' => 'article',
            'id' => '1',
            'title' => 'Ошибка соединения',
            'description' => $error,
            'input_message_content'=>[
                'message_text'=> '/options',
                'parse_mode'=> 'html',
                'disable_web_page_preview'=> true,
            ],
        ];

        $this->answerInlineQuery([
            'inline_query_id' => $inlineQueryId,
            'is_personal' => true,
            'results'=> Json::encode($result)
        ]);
        return ['message' => 'ok', 'code' => 200];
    }


    private function debug (){



        $start_time = time();

        while(true) {

            if ((time() - $start_time) > 10) {
                $serverError = [];
                $serverError['error'] = 1;
                $serverError['message'] = 'Извините, B2B сервер не отвечает'
                    .PHP_EOL .'В данный момент запрос не может быть обработан';
                $serverError['code'] = 500;
                return Json::encode($serverError);

//                $this->sendMessage([
//                    'chat_id' => $this->user['telegram_user_id'],
//                    'text' => 'выскочили по таймауту',
//                ]);
//                return 'ok';
            }
            // Other processing
            $this->sendMessage([
                'chat_id' => $this->user['telegram_user_id'],
                'text' => 'перед таймаутом',
            ]);
            sleep(15);

        }

//        $this->sendMessage([
//            'chat_id' => $this->user['telegram_user_id'],
//            'text' => 'перед таймаутом',
//        ]);
//        sleep(15);



        $this->sendMessage([
            'chat_id' => $this->user['telegram_user_id'],
            'text' => 'после таймаута',
        ]);





//        return $this->settings;
        return true;

    }

}

