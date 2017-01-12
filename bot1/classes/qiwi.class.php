<?php
class QIWI {
    public $iQiwiAccount, $aBalances = array( 'USD' => 0, 'RUB' => 0, 'EUR' => 0, 'KZT' => 0 );
    private $sCookieFile, $sProxy;
   
    # Конструктор - инициализирует объект класса.
    # Принимает: qiwi.кошелек (в международном формате без +), пароль, прокси (не обязательно).
     public function __construct( $iQiwiAccount, $sPassword, $sCookieFile, $sProxy = null ) {
        
        # Если файл cookie не существует :
        if( !is_file( $sCookieFile ) ) {
            
            # Если директория недоступна для записи :
            if( !is_writeable( dirname( $sCookieFile ) ) ) {
                
                # Выбрасываем исключение :
                throw new Exception( 'please, set chmod 777 for directory '.dirname( $sCookieFile ) );
            }
            
            # Создание файла :
            file_put_contents( $sCookieFile, '' );
        }
        
        # Если файл cookie недоступен для записи :
        if( !is_writable( $sCookieFile ) ) {
            
            # Выбрасываем исключение :
            throw new Exception( 'please, set chmod 777 for file '.$sCookieFile );
        }
 
        # Инициализация данных класса :
        $this->sCookieFile = $sCookieFile;
        $this->sProxy = $sProxy;

        # Запрос к серверу :
        $this->curl( 'person/state.action' );
        
        # Проверка авторизации кошельком :
        if( isset( $this->aResponse['data'] ) && isset( $this->aResponse['data']['person'] ) && isset( $this->aResponse['data']['balances'] ) && $this->aResponse['data']['person'] == $iQiwiAccount ) {
            
            # Инициализация переменных класса :
            $this->iQiwiAccount = $this->aResponse['data']['person'];
            
            # Перебираем информацию о балансах :
            foreach( $this->aResponse['data']['balances'] as $sEquivalent => $dBalance ) {
                
                # Добавляем данные в массив :
                $this->aBalances[$sEquivalent] = $dBalance;
            }
            return;
        }
        
        # Запрос к серверу :
        $this->curl( 'https://auth.qiwi.com/cas/tgts', json_encode( array( 'login' => '+'.$iQiwiAccount, 'password' => $sPassword ) ) );
        
        # Если в ответе есть ошибка :
        if( isset( $this->aResponse['entity'] ) && isset( $this->aResponse['entity']['error'] ) && isset( $this->aResponse['entity']['error']['message'] ) )
            throw new Exception( mb_strtolower( $this->aResponse['entity']['error']['message'] ) );
        
        # Если в ответе нет токена :
        if( !isset( $this->aResponse['entity'] ) || !isset( $this->aResponse['entity']['ticket'] ) )
            throw new Exception( 'ticket не найден - '.$this->sResponse );
        
        # Тут у нас TGT токен
        $sTGTToken = $this->aResponse['entity']['ticket'];
        
        # Запрос к серверу :
        $this->curl( 'https://auth.qiwi.com/cas/sts', json_encode( array( 'service' => 'https://qiwi.com/j_spring_cas_security_check', 'ticket' => $sTGTToken ) ) );
        
        # Если в ответе есть ошибка :
        if( isset( $this->aResponse['entity'] ) && isset( $this->aResponse['entity']['error'] ) && isset( $this->aResponse['entity']['error']['message'] ) )
            throw new Exception( mb_strtolower( $this->aResponse['entity']['error']['message'] ) );
        
        # Если в ответе нет токена :
        if( !isset( $this->aResponse['entity'] ) || !isset( $this->aResponse['entity']['ticket'] ) )
            throw new Exception( 'ticket не найден - '.$this->sResponse );
        
        # Запрос к серверу :
        $this->curl( 'https://qiwi.com/j_spring_cas_security_check?ticket='.$this->aResponse['entity']['ticket'] );
        
        # Если в ответе есть ошибка :
        if( isset( $this->aResponse['message'] ) && $this->aResponse['message'] != '' )
            throw new Exception( $this->aResponse['message'] );
        
        # Если авторизация не успешная :
        if( !isset( $this->aResponse['code'] ) || !isset( $this->aResponse['code']['_name'] ) || $this->aResponse['code']['_name'] != 'NORMAL' )
            throw new Exception( 'error authorize - '.$this->sResponse );
        
        # Получение информации об qiwi.кошельке :
        $this->curl( 'person/state.action' );

        # Если возникла ошибка:
        if( !isset( $this->aResponse['data'] ) || !is_array( $this->aResponse['data'] ) || !isset( $this->aResponse['data']['person'] ) || !isset( $this->aResponse['data']['balances'] ) )
            throw new Exception( var_export( $this->aResponse, true ) );
        
        # Инициализация переменных класса :
        $this->iQiwiAccount = $this->aResponse['data']['person'];
        
        # Перебираем информацию о балансах :
        foreach( $this->aResponse['data']['balances'] as $sEquivalent => $dBalance ) {
            
            # Инициализация переменных класса :
            $this->aBalances[$sEquivalent] = $dBalance;
        }
    }
    
    # Метод : перевод средств.
    # Принимает : киви кошелек, сумма, валюта, примечание.
    # Возвращает : № транзакции.
    public function SendMoney( $iQiwiAccount, $dAmount, $sCurrency, $sComment ) {
        return $this->payment( null, array( 'account' => '+'.$iQiwiAccount, 'comment' => $sComment ), $dAmount, $sCurrency, $sCurrency );
    }
   
    public function SvyaznoyBankTransfer( $iCard, $iOperType, $dAmount, $sComment ) {
        return $this->payment( 23022, array( 'account' => $iCard, 'account_type' => 1, 'comment' => $sComment, 'mfo' => '044583139', 'oper_type' => $iOperType, 'pay_type' => 3005, 'pfp' => 1388597437 ), $dAmount );
    }
    public function TCSBankTransfer( $iAccountType, $iCard, $iOperType, $dAmount, $sComment ) {
        return $this->payment( 466, array( 'account' => $iCard, 'account_type' => $iAccountType, 'comment' => $sComment, 'mfo' => '', 'oper_type' => $iOperType, 'pay_type' => 3005, 'pfp' => 1388597437 ), $dAmount );
    }
    public function AlfaBankTransfer( $iAccountType, $iAccount, $iOperType, $dAmount, $sComment, $sExpDate = '' ) {
        return $this->payment( 464, array( 'account' => $iAccount, 'account_type' => $iAccountType, 'comment' => $sComment, 'exp_date' => $sExpDate, 'mfo' => '044525593', 'oper_type' => $iOperType, 'pay_type' => 3005, 'pfp' => 1388597437 ), $dAmount );
    }
    public function RussianStandartBankTransfer( $iAccountType, $iCard, $iOperType, $dAmount, $sComment ) {
        return $this->payment( 815, array( 'account' => $iCard, 'account_type' => $iAccountType, 'comment' => $sComment, 'mfo' => '044583151', 'oper_type' => $iOperType, 'pay_type' => 3005, 'pfp' => 1388597437 ), $dAmount );
    }
    public function MoskoPrivatBankTransfer( $iCard, $iOperType, $dAmount, $sComment ) {
        return $this->payment( 830, array( 'card_num' => $iCard, 'account_type' => '1', 'comment' => $sComment, 'mfo' => '044585342', 'oper_type' => $iOperType, 'pay_type' => 3005, 'pfp' => 1388597437 ), $dAmount );
    }
    public function BankCardTransfer( $iCard, $dAmount, $sComment ) {
        
        # В зависимости от провайдера :
        switch( $this->getCardProvider( $iCard ) ) {
            
            # MasterCard MoneySend (Международный перевод) :
            case 21013:
                return $this->payment( 21013, array( 'account' => $iCard, 'exp_date' => '', 'comment' => $sComment ), $dAmount );
        
            # MasterCard MoneySend (Международный перевод) :
            case 21012:
                return $this->payment( 21012, array( 'rem_name_f' => 'Пупкин', 'rem_name' => 'Василий', 'rec_country' => 'Москва', 'rec_city' => 'Россия', 'rec_address' => 'ул. Пушкинская, 19', 'account' => $iCard, 'exp_date' => '', 'comment' => $sComment ), $dAmount );
        
            # Visa Personal Payments (Россия) :
            case 1963:
                return $this->payment( 1963, array( 'account' => $iCard, 'comment' => $sComment ), $dAmount );
                
            # Visa Personal Payments (Международный перевод) :
            case 1960:
                return $this->payment( 1960, array( 'rem_name_f' => 'Пупкин', 'rem_name' => 'Василий', 'rec_country' => 'Москва', 'rec_city' => 'Россия', 'rec_address' => 'ул. Пушкинская, 19', 'account' => $iCard, 'comment' => $sComment ), $dAmount );
        }
        
        throw new Exception( 'provider not programming' );
    }
    
    public function VisaTransfer( $iCard, $dAmount, $sComment ) {
        return $this->payment( 21013, array( 'account' => substr( $iCard, 0, 4 ).'-'.substr( $iCard, 4, 4 ).'-'.substr( $iCard, 8, 4 ).'-'.substr( $iCard, -4 ), 'comment' => $sComment, 'rec_address' => '', 'rec_city' => '', 'rec_country' => '', 'rem_name' => '', 'rem_name_f' => '' ), $dAmount );
    }
    public function MasterCardRUTransfer( $iCard, $sExpDate, $dAmount, $sComment ) {
        return $this->payment( 21013, array( 'account' => $iCard, 'comment' => $sComment, 'exp_date' => $sExpDate, 'rec_address' => '', 'rec_city' => '', 'rec_country' => '', 'rem_name' => '', 'rem_name_f' => '' ), $dAmount );
    }
    public function MasterCardINTTransfer( $iCard, $sSenderLastName, $sSenderFirstName, $sReceiverCountry, $sReceiverCity, $sReceiverAddress, $sExpDate, $dAmount, $sComment ) {
        return $this->payment( 21012, array( 'account' => $iCard, 'comment' => $sComment, 'exp_date' => $sExpDate, 'rec_address' => $sReceiverAddress, 'rec_city' => $sReceiverCity, 'rec_country' => $sReceiverCountry, 'rem_name' => $sSenderFirstName, 'rem_name_f' => $sSenderLastName ), $dAmount );
    }
    public function WebMoneyTransfer( $sWMRPurse, $dAmount, $sComment ) {
        return $this->payment( 56, array( 'account' => $sWMRPurse, 'comment' => $sComment ), $dAmount );
    }
    public function PrivatMoneyTransfer( $sFromFirstName, $sFromLastName, $sFromMiddleName, $sToFirstName, $sToLastName, $sToMiddleName, $iToCountry, $iToPhone, $dAmount, $sComment ) {
        return $this->payment( 20243, array( 'account' => '+'.$iToPhone, 'comment' => $sComment, 'control_code' => '', 'country' => $iToCountry, 'from_name' => $sFromFirstName, 'from_name_f' => $sFromLastName, 'from_name_p' => $sFromMiddleName, 'rec_amount' => $dAmount, 'rec_course' => 1, 'rec_currency' => 810, 'to_name' => $sToFirstName, 'to_name_f' => $sToLastName, 'to_name_p' => $sToMiddleName ), $dAmount );
    }
    public function AnelikTransfer( $iRemitent, $iPin, $dAmount, $sComment ) {
        return $this->payment( 1895, array( 'remitent' => $iRemitent, 'pin' => $iPin, 'comment' => $sComment ), $dAmount );
    }
    public function VTB24Transfer( $iAccount, $iUrgent, $sBirthday, $sBirthplace, $sFirstName, $sLastName, $sMiddleName, $iMFO, $iOperType, $dAmount, $sComment ) {
        return $this->paymentBank( 816, array( 'account' => $iAccount, 'account_type' => '2', 'bdate' => $sBirthday, 'bplace' => $sBirthplace, 'comment' => $sComment, 'fname' => $sFirstName, 'lname' => $sLastName, 'mfo' => $iMFO, 'mname' => $sMiddleName, 'oper_type' => $iOperType, 'pay_type' => '3005', 'pfp' => '1396686537', 'urgent' => $iUrgent ), $dAmount );
    }
    public function SberBankTransfer( $iAccount, $iUrgent, $sBirthday, $sBirthplace, $sFirstName, $sLastName, $sMiddleName, $iMFO, $iOperType, $dAmount, $sComment ) {
        return $this->paymentBank( 870, array( 'account' => $iAccount, 'account_type' => '2', 'bdate' => $sBirthday, 'bplace' => $sBirthplace, 'comment' => $sComment, 'fname' => $sFirstName, 'lname' => $sLastName, 'mfo' => $iMFO, 'mname' => $sMiddleName, 'oper_type' => $iOperType, 'pay_type' => '3005', 'pfp' => '1388597437', 'urgent' => $iUrgent ), $dAmount );
    }
    public function RaiffeisenbankTransfer( $iAccount, $iUrgent, $sBirthday, $sBirthplace, $sFirstName, $sLastName, $sMiddleName, $iMFO, $iOperType, $dAmount, $sComment ) {
        return $this->paymentBank( 872, array( 'account' => $iAccount, 'account_type' => '2', 'bdate' => $sBirthday, 'bplace' => $sBirthplace, 'comment' => $sComment, 'fname' => $sFirstName, 'lname' => $sLastName, 'mfo' => $iMFO, 'mname' => $sMiddleName, 'oper_type' => $iOperType, 'pay_type' => '3005', 'pfp' => '1396685698', 'urgent' => $iUrgent ), $dAmount );
    }
    public function EuropeBankTransfer( $iAccount, $iAccountType, $sBirthday, $sBirthplace, $sFirstName, $sLastName, $sMiddleName, $iOperType, $dAmount, $sComment ) {
        return $this->payment( 931, array( 'account' => $iAccount, 'account_type' => $iAccountType, 'bdate' => $sBirthday, 'bplace' => $sBirthplace, 'comment' => $sComment, 'exp_date' => '', 'fname' => $sFirstName, 'lname' => $sLastName, 'mfo' => '044525767', 'mname' => $sMiddleName, 'oper_type' => $iOperType, 'pfp' => '1388597437' ), $dAmount );
    }
    public function SkypeTransfer( $sAccount, $dAmount, $sComment = '' ) {
        return $this->payment( 23195, array( 'account' => $sAccount, 'comment' => $sComment ), $dAmount, 'USD' );
    }
    public function OdnoklassnikiTransfer( $sAccount, $dAmount, $sComment = '' ) {
        return $this->payment( 1746, array( 'account' => $sAccount, 'comment' => $sComment ), $dAmount );
    }
    public function SvyaznoyBank( $iEAN, $dAmount, $sComment = '', $iOperType = 3 ) {
        return $this->payment( 23022, array( 'account' => $iEAN, 'oper_type' => $iOperType, 'comment' => $sComment ), $dAmount );
    }
    public function InvoicePayment( $iPhone, $dAmount, $sCurrency, $sComment ) {
        
        # Распознавание суммы платежа :
        $dAmount = intval( str_replace( ',', '.', $dAmount ) * 100 ) / 100;
        $aAmount = explode( '.', $dAmount );
        if( !isset( $aAmount[1] ) )
            $aAmount[1] = '00';
        else if( strlen( $aAmount[1] ) != 2 )
            $aAmount[1] .= '0';

        # Отправка запроса на выписку счета :
        $sResponse = $this->curl( 'user/order/create.action?to=%2B'.$iPhone.'&value='.$aAmount[0].'&change='.$aAmount[1].'&amount='.$dAmount.'&currency='.$sCurrency.'&comment='.urlencode( $sComment ) );

        # Если сервер вернул ошибку :
        if( mb_strpos( $sResponse, 'ERROR' ) !== false ) {
            $aExplode = explode( '"message":"', $sResponse );
            $aExplode = explode( '"', $aExplode[1] );
            throw new Exception( $aExplode[0], 1 );
        }
        else if( mb_strpos( $sResponse, '{"value":"0","_name":"NORMAL"}' ) === false ) {
            $aExplode = explode( '<p class="errorMarker">', $sResponse );
            $aExplode = explode( '</p>', $aExplode[1] );
            throw new Exception( $aExplode[0], 1 );
        }
        
        $aHistory = $this->GetInvoices( date( 'd.m.Y', strtotime( '-1 day' ) ), date( 'd.m.Y', strtotime( '+1 day' ) ) );
        $aInvoice = array_shift( $aHistory );
        if( $aInvoice === false || $aInvoice['dAmount'] != $dAmount || $aInvoice['iOpponentPhone'] != $iPhone || $aInvoice['sStatus'] != 'NOT_PAID' )
            throw new Exception( 'invoice not found in history: '.var_export( $aInvoice, true ) );
        return array( 'iInvoiceID' => $aInvoice['iID'], 'iOrderID' => $aInvoice['iOrderID'] );
    }
    public function GetHistory( $sStartDate, $sFinishDate ) {
        
        # Получение списка транзакций :
        $sResult = $this->curl( 'user/report/list.action?daterange=true&start='.$sStartDate.'&finish='.$sFinishDate );
 
        $aTransactions = array();
        foreach( explode( '</div><div class="reportsLine ', str_replace( '> <', '><', preg_replace( '!\s+!u', ' ', $sResult ) ) ) as $iKey => $sValue ) {
            if( $iKey == 0 )
                continue;
            
            $aData = array();

            # Получение суммы счета :
            $aData['iID'] = explode( '<span class="value">', $sValue );
            if( count( $aData['iID'] ) < 2 )
                continue;
            $aData['iID'] = explode( '</', $aData['iID'][1] );
            $aData['iID'] = trim( $aData['iID'][0] );
            
            # Получение даты и время :
            $aData['sDate'] = explode( 'class="date">', $sValue );
            $aData['sDate'] = explode( '</', $aData['sDate'][1] );
            $aData['sDate'] = trim( $aData['sDate'][0] );
            $aData['sTime'] = explode( 'class="time">', $sValue );
            $aData['sTime'] = explode( '</', $aData['sTime'][1] );
            $aData['sTime'] = trim( $aData['sTime'][0] );
            
            # Получение суммы :
            $aData['sAmount'] = explode( 'class="originalExpense"><span>', $sValue );
            $aData['sAmount'] = explode( '</', $aData['sAmount'][1] );
            $aData['sAmount'] = trim( $aData['sAmount'][0] );
            $aData['dAmount'] = preg_replace( '/[^0-9\.]+/', '', str_replace( ',', '.', $aData['sAmount'] ) ) - 0;
            
            # Получение валюты счета :
            $aData['sCurrency'] = mb_strpos( $aData['sAmount'], 'руб.' ) !== false ? 'RUB' : (mb_strpos( $aData['sAmount'], 'долл.' ) !== false ? 'USD' : (mb_strpos( $aData['sAmount'], 'тенге.' ) !== false ? 'KZT' : 'NAN'));
            
            # Получение суммы с учетом комиссии :
            $aData['sWithExpend'] = explode( 'WithExpend', $sValue );
            $aData['sWithExpend'] = explode( '</div>', $aData['sWithExpend'][1] );
            $aData['sWithExpend'] = explode( '<div class="cash">', $aData['sWithExpend'][0] );
            $aData['sWithExpend'] = trim( $aData['sWithExpend'][1] );
            $aData['dWithExpend'] = preg_replace( '/[^0-9\.]+/', '', str_replace( ',', '.', $aData['sWithExpend'] ) ) - 0;
            
            # Получение номера телефона корреспондента :
            $aData['iOpponentPhone'] = explode( 'class="opNumber">', $sValue );
            $aData['iOpponentPhone'] = explode( '</', $aData['iOpponentPhone'][1] );
            $aData['iOpponentPhone'] = trim( str_replace( '+', '', $aData['iOpponentPhone'][0] ) );
            
            # Получение примечания :
            $aData['sComment'] = explode( 'class="comment">', $sValue );
            $aData['sComment'] = explode( '</', $aData['sComment'][1] );
            $aData['sComment'] = html_entity_decode( trim( $aData['sComment'][0] ), ENT_QUOTES, 'UTF-8' );
            
            # Получаем информацию о провайдере :
            $aData['sProvider'] = explode( '<div class="provider"><span>', $sValue );
            $aData['sProvider'] = explode( '</span>', $aData['sProvider'][1] );
            $aData['sProvider'] = trim( $aData['sProvider'][0] );
            
            # Прибыль или расход ?
            $aData['sType'] = mb_strpos( $sValue, 'IncomeWithExpend expenditure' ) !== false ? 'EXPENDITURE' : (mb_strpos( $sValue, 'IncomeWithExpend income' ) !== false ? 'INCOME' : 'NAN');
            
            # Получение статуса транзакции :
            $aData['sStatus'] = explode( '"', $sValue );
            $aData['sStatus'] = str_replace( 'status_', '', trim( $aData['sStatus'][0] ) );
            
            # Получаем  информацию о ошибке если она есть :
            if( $aData['sStatus'] == 'ERROR' ) {
                $aData['sError'] = explode( '{"message":"', $sValue );
                $aData['sError'] = explode( '"', $aData['sError'][1] );
                $aData['sError'] = trim( $aData['sError'][0] );
            }
            
            # Дополнительные проверки :
            if( $aData['iID'] == false ) {
                $aData['iID'] = explode( '{"txn":', $sValue );
                $aData['iID'] = explode( '}', $aData['iID'][1] );
                $aData['iID'] = $aData['iID'][0];
            }
            
            $aTransactions['ID-'.$aData['iID']] = $aData;
        }
        return $aTransactions;
    }
    public function GetInvoices( $sStartDate, $sFinishDate ) {
        
        # Получение списка выписанных счетов :
        $sResult = $this->curl( 'user/order/list.action?daterange=true&start='.$sStartDate.'&finish='.$sFinishDate.'&conditions.directions=out' );

        $aTransactions = array();
        foreach( explode( '<div class="ordersLine ', str_replace( '> <', '><', preg_replace( '!\s+!u', ' ', $sResult ) ) ) as $iKey => $sValue ) {
            if( $iKey == 0 )
                continue;
            
            $aData = array();
            
            # Получение суммы счета :
            $aData['iID'] = explode( 'class="transaction"><span>', $sValue );
            $aData['iID'] = explode( '</', $aData['iID'][1] );
            $aData['iID'] = trim( $aData['iID'][0] );
            
            # Получение даты выписки счета :
            $aData['sCreateDate'] = explode( 'class="orderCreationDate">', $sValue );
            $aData['sCreateDate'] = explode( '</', $aData['sCreateDate'][1] );
            $aData['sCreateDate'] = trim( $aData['sCreateDate'][0] );
            
            # Получение суммы и валюты счета :
            $aData['sAmount'] = explode( 'class="amount">', $sValue );
            $aData['sAmount'] = explode( '</', $aData['sAmount'][1] );
            $aData['sAmount'] = trim( $aData['sAmount'][0] );
            
            # Получение суммы счета :
            $aData['dAmount'] = preg_replace( '/[^0-9\.]+/', '', str_replace( ',', '.', $aData['sAmount'] ) ) - 0;
            
            # Получение валюты счета :
            $aData['sCurrency'] = mb_strpos( $aData['sAmount'], 'евро.' ) !== false ? 'EUR' : (mb_strpos( $aData['sAmount'], 'руб.' ) !== false ? 'RUB' : (mb_strpos( $aData['sAmount'], 'долл.' ) !== false ? 'USD' : (mb_strpos( $aData['sAmount'], 'тенге.' ) !== false ? 'KZT' : 'NAN')));
            
            # Получение номера телефона корреспондента :
            $aData['iOpponentPhone'] = explode( 'class="from"><span>', $sValue );
            $aData['iOpponentPhone'] = explode( '</', $aData['iOpponentPhone'][1] );
            $aData['iOpponentPhone'] = trim( $aData['iOpponentPhone'][0] );
            
            # Получение примечания :
            $aData['sComment'] = explode( 'class="commentItem">', $sValue );
            $aData['sComment'] = explode( '</', $aData['sComment'][1] );
            $aData['sComment'] = trim( $aData['sComment'][0] );
            
            # Получение даты оплаты счета :
            $aData['sPayDate'] = explode( 'class="payDate">', $sValue );
            $aData['sPayDate'] = explode( '</', $aData['sPayDate'][1] );
            $aData['sPayDate'] = trim( $aData['sPayDate'][0] );
            
            # Получение статуса транзакции :
            $aData['sStatus'] = explode( '"', $sValue );
            $aData['sStatus'] = str_replace( 'status_', '', trim( $aData['sStatus'][0] ) );
            
            if( $aData['sStatus'] == 'NOT_PAID' ) {
                $aData['iOrderID'] = explode( '{"data":{"order":"', $sValue );
                $aData['iOrderID'] = explode( '"', $aData['iOrderID'][1] );
                $aData['iOrderID'] = $aData['iOrderID'][0];
            }
            
            $aTransactions['ID-'.$aData['iID']] = $aData;
        }
        return $aTransactions;
    }
    public function GetBalances() {
        if( ($aResponse = @json_decode( $this->curl( 'person/state.action' ), true )) === false )
            throw new Exception( 'internal error' );
        else if( !is_array( $aResponse['data'] ) || !isset( $aResponse['data']['person'] ) || !isset( $aResponse['data']['balances'] ) )
            throw new Exception( var_export( $aResponse['data'], true ) );
        $this->iQiwiAccount = $aResponse['data']['person'];
        foreach( $aResponse['data']['balances'] as $sEquivalent => $dBalance )
            $this->aBalances[$sEquivalent] = $dBalance;
        return $this->aBalances;
    }
    public function CancelInvoice( $iOrderID ) {
        if( ($aResponse = @json_decode( $this->curl( 'user/order/reject.action', 'order='.$iOrderID ), true )) === false || !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( 'internal error' );
        if( ($aResponse = @json_decode( $this->curl( 'user/order/reject.action', 'order='.$iOrderID.'&token='.$aResponse['data']['token'] ), true )) === false )
            throw new Exception( 'internal error' );
        return isset( $aResponse['code'] ) && isset( $aResponse['code']['value'] ) && $aResponse['code']['value'] == 0;
    }
    public function payment( $iProvider, array $aExtra, $dAmount, $sCurrency = 'RUB', $sPayCurrency = 'RUB' ) {
        
        # Распознавание суммы платежа :
        $dAmount = intval( str_replace( ',', '.', $dAmount ) * 100 ) / 100;
        $aAmount = explode( '.', $dAmount );
        if( !isset( $aAmount[1] ) )
            $aAmount[1] = '00';
        else if( strlen( $aAmount[1] ) != 2 )
            $aAmount[1] .= '0';
        
        # Преобразовуем массив aExtra :
        foreach( $aExtra as $sKey => $sValue ) {
            $aExtra["extra['".$sKey."']"] = $sValue;
            unset( $aExtra[$sKey] );
        }
        
        # Запрос на получение формы платежа :
        $this->curl( is_null( $iProvider ) ? 'payment/transfer/form.action' : 'payment/form.action?provider='.$iProvider );
        
        # Запрос на получение токена платежа :
        if( ($aResponse = @json_decode( $this->curl( 
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sPayCurrency,
                    'state' => 'CONFIRM' 
                ), $aExtra )
            )
        ), true )) === false )
            throw new Exception( 'internal error, step 1' );
        
        # Проверка статуса платежа :
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 2' );
            
        # Проведения платежа в системе :
        $sResponse = $this->curl(
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sPayCurrency,
                    'state' => 'CONFIRM',
                    'token' => $aResponse['data']['token'],
                ), $aExtra )
            )
        );

        # Запрос на получение токена подтверждения платежа :
        if( ($aResponse = @json_decode( $this->curl( 'payment/form/state.action?state=PAY' ), true )) === false )
            throw new Exception( 'internal error, step 3' );
        
        # Проверка статуса платежа :
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 4' );

        # Подтвеждение проведения платежа :
        $sResponse = $this->curl(
            'payment/form/state.action',
            array(
                'token' => $aResponse['data']['token'],
                'state' => 'PAY'
            )
        );

        # Если платеж был не успешен :
        if( mb_strpos( $sResponse, 'transaction":"' ) === false ) {
            if( mb_strpos( $sResponse, 'class="errorElement"' ) !== false ) {
                $aExplode = explode( 'class="errorElement">', $sResponse );
                $aExplode = explode( '</', $aExplode[1] );
                throw new Exception( trim( $aExplode[0] ) );
            }
            # Иначе, если требуется SMS подтверждение операций :
            else if( mb_strpos( $sResponse, 'confirmPage' ) !== false )
                return false;
            else {
                if( count( $sMessage = explode( '<p>', $sResponse ) ) < 2 )
                    throw new Exception( 'unknown error' );
                $sMessage = explode( '</p>', $sMessage[1] );
                throw new Exception( $sMessage[0] );
            }
        }
        
        # Если провайдер - Qiwi Яйца :
        if( $iProvider == 22496 ) {
            if( count( $aExplode = explode( 'Код ваучера:', $sResponse ) ) < 2 )
                throw new Exception( 'error parse egg' );
            $aExplode = explode( '</', $aExplode[1] );
            return trim( $aExplode[0] );
        }
            
        # Получение истории переводов :
        $aHistory = $this->GetHistory( date( 'd.m.Y', strtotime( '-1 day' ) ), date( 'd.m.Y', strtotime( '+1 day' ) ) );
        $aTransfer = array_shift( $aHistory );
        if( $aTransfer === false || $aTransfer['dAmount'] != $dAmount || $aTransfer['sCurrency'] != $sCurrency )
            throw new Exception( 'transfer not found in history' );
        return $aTransfer['iID'];
    }
    private function paymentBank( $iProvider, array $aExtra, $dAmount, $sCurrency = 'RUB' ) {

        # Распознавание суммы платежа :
        $dAmount = intval( str_replace( ',', '.', $dAmount ) * 100 ) / 100;
        $aAmount = explode( '.', $dAmount );
        if( !isset( $aAmount[1] ) )
            $aAmount[1] = '00';
        else if( strlen( $aAmount[1] ) != 2 )
            $aAmount[1] .= '0';
        
        # Преобразовуем массив aExtra :
        foreach( $aExtra as $sKey => $sValue ) {
            $aExtra["extra['".$sKey."']"] = $sValue;
            unset( $aExtra[$sKey] );
        }

        # Загружаем страницу провайдера :
        $this->curl( 'payment/form.action?provider='.$iProvider );
        
        # Отправляем FIELD запросы :
        if( ($aResponse = @json_decode( $this->curl( 
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sCurrency,
                    'state' => 'FIELD' 
                ), $aExtra )
            )
        ), true )) === false )
            throw new Exception( 'internal error, step 1' );
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 2' );
        $sResponse = $this->curl(
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sCurrency,
                    'state' => 'FIELD',
                    'token' => $aResponse['data']['token'],
                ), $aExtra )
            )
        );
        
        # Отправляем CONFIRM запросы :
        if( ($aResponse = @json_decode( $this->curl( 
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sCurrency,
                    'state' => 'CONFIRM' 
                ), $aExtra )
            )
        ), true )) === false )
            throw new Exception( 'internal error, step 3' );
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 4' );
        $sResponse = $this->curl(
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => $aAmount[0],
                    'amountFraction' => $aAmount[1],
                    'arg_num' => '',
                    'currency' => $sCurrency,
                    'protected' => 'true',
                    'source' => 'qiwi_'.$sCurrency,
                    'state' => 'CONFIRM',
                    'token' => $aResponse['data']['token'],
                ), $aExtra )
            )
        );
        
        # Запрос на получение токена подтверждения платежа :
        if( ($aResponse = @json_decode( $this->curl( 'payment/form/state.action?state=PAY' ), true )) === false )
            throw new Exception( 'internal error, step 3' );
        
        # Проверка статуса платежа :
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 4' );

        # Подтвеждение проведения платежа :
        $sResponse = $this->curl(
            'payment/form/state.action',
            array(
                'token' => $aResponse['data']['token'],
                'state' => 'PAY'
            )
        );

        # Если платеж был не успешен :
        if( mb_strpos( $sResponse, 'transaction":"' ) === false ) {
            if( mb_strpos( $sResponse, 'class="errorElement"' ) !== false ) {
                $aExplode = explode( 'class="errorElement">', $sResponse );
                $aExplode = explode( '</', $aExplode[1] );
                throw new Exception( trim( $aExplode[0] ) );
            }
            else {
                if( count( $sMessage = explode( '<p>', $sResponse ) ) < 2 )
                    throw new Exception( 'unknown error' );
                $sMessage = explode( '</p>', $sMessage[1] );
                throw new Exception( $sMessage[0] );
            }
        }

        $aHistory = $this->GetHistory( date( 'd.m.Y', strtotime( '-1 day' ) ), date( 'd.m.Y', strtotime( '+1 day' ) ) );
        $aTransfer = array_shift( $aHistory );
        if( $aTransfer === false || $aTransfer['dAmount'] != $dAmount || $aTransfer['sCurrency'] != $sCurrency )
            throw new Exception( 'transfer not found in history' );
        return $aTransfer['iID'];
    }
    
    # Метод : определение ID провайдера оплаты по номеру банковской карты.
    public function getCardProvider( $iCard ) {
        
        # Загрузка формы :
        $this->curl( 'payment/form.action?provider=1963' );
        
        # Предварительный запрос :
        if( ($aResponse = @json_decode( $this->curl( 
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => 10,
                    'amountFraction' => 0,
                    'arg_num' => '',
                    'currency' => 'RUB',
                    'protected' => 'true',
                    'source' => 'qiwi_RUB',
                    'state' => 'FIELD' 
                ), array( "extra['account']" => $iCard ) )
            )
        ), true )) === false )
            throw new Exception( 'internal error, step 1' );
        if( !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : 'internal error, step 2' );

        # Запрос подтверждения :
        $sResponse = $this->curl(
            'user/payment/form/state.action?'.http_build_query( 
                array_merge( array(
                    'amountInteger' => 10,
                    'amountFraction' => 1,
                    'arg_num' => '',
                    'currency' => 'RUB',
                    'protected' => 'true',
                    'source' => 'qiwi_RUB',
                    'state' => 'FIELD',
                    'token' => $aResponse['data']['token'],
                ), array( "extra['account']" => $iCard ) )
            )
        );
        if( count( $aExplode = explode( '{"provider":', $sResponse ) ) < 2 || count( $aExplode = explode( ',', $aExplode[1] ) ) < 2 )
            throw new Exception( 'provider not found' );
        return trim( $aExplode[0] ) - 0;
    }
     private function curl( $sPath, $mPOST = null, array $aOptions = null ) {
        
        # Инициализация статических переменных :
        static $sReferer = null;
        
        # Инициализация переменных :
        $oCurl = curl_init( mb_substr( $sPath, 0, 4 ) == 'http' ? $sPath : 'https://qiwi.com/'.$sPath );
        
        # Настройки cURL :
        curl_setopt_array( $oCurl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->sCookieFile,
            CURLOPT_COOKIEFILE => $this->sCookieFile,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => mb_substr( $sPath, 0, 4 ) == 'http' ? (is_null( $mPOST ) ? array( 'Accept: application/json, text/javascript, */*; q=0.01', 'X-Requested-With: XMLHttpRequest' ) : array( 'Content-Type: application/json; charset=UTF-8' )) : array( 'Accept: application/json, text/javascript, */*; q=0.01', 'X-Requested-With: XMLHttpRequest' ),
        ) );
        
        # Если требуется отправить POST - запрос :
        if( is_array( $mPOST ) || $mPOST != '' || mb_substr( $sPath, 0, 4 ) != 'http' ) {
            
            # Настройки Curl подключения :
            curl_setopt_array( $oCurl, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => is_array( $mPOST ) ? http_build_query( $mPOST ) : $mPOST,
            ) );
        }
        
        # Если существует реферер :
        if( !is_null( $sReferer ) )
            curl_setopt( $oCurl, CURLOPT_REFERER, $sReferer );
        
        # Если требуется указать дополнительные настройки :
        if( is_array( $aOptions ) && count( $aOptions ) )
            curl_setopt_array( $oCurl, $aOptions );
        
        # Если требуется работать через Proxy :
        if( $this->sProxy != '' ) {
            
            # Разбиваем строку по двоеточию в массив :
            $aExplode = explode( ':', $this->sProxy );
            
            # Если размер массива > 2 :
            if( count( $aExplode ) > 2 ) {
                
                # Подключение proxy к curl:
                curl_setopt_array( $oCurl, array(
                    CURLOPT_PROXY => $aExplode[0].':'.$aExplode[1],
                    CURLOPT_HTTPPROXYTUNNEL => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_PROXYTYPE => $aExplode[2] == 'socks5' ? CURLPROXY_SOCKS5 : ($aExplode[2] == 'socks4' ? CURLPROXY_SOCKS4 : CURLPROXY_HTTP)
                ) );
                
                # Если размер массива больше 4 :
                if( count( $aExplode ) > 4 ) {
                    
                    # Авторизация в proxy к curl:
                    curl_setopt( $oCurl, CURLOPT_PROXYUSERPWD, $aExplode[3].':'.$aExplode[4] );
                }
            }          
        }

        # Получение ответа :
        $this->sResponse = curl_exec( $oCurl );
        
        # Если произошла ошибка :
        if( curl_errno( $oCurl ) )
            throw new Exception( curl_errno( $oCurl ).' - '.curl_error( $oCurl ) );
        
        # Закрываем соединение :
        curl_close( $oCurl );
        
        # Сохраняем страницу referer :
        $sReferer = mb_substr( $sPath, 0, 4 ) == 'http' ? $sPath : 'https://qiwi.com/'.$sPath;
        
        # Преобразование ответа в массив :
        $this->aResponse = json_decode( $this->sResponse, true );
        if( json_last_error() != JSON_ERROR_NONE )
            $this->aResponse = array();
        
        return $this->sResponse;
    }
    public function phoneToProvider( $iPhone ) {
        $aResponse = json_decode( $this->curl( 'mobile/detect.action', array( 'phone' => '+'.$iPhone ) ), true );
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['_name'] ) )
            throw new Exception( json_encode( $aResponse ) );
        if( $aResponse['code']['_name'] != 'NORMAL' )
            throw new Exception( $aResponse['message'] );
        return $aResponse['message'] - 0;
    }
    public function payPhone( $iPhone, $dAmount, $sComment ) {
        
        # Определяем провайдер по номеру :
        $iProvider = $this->phoneToProvider( $iPhone );
                
        return $this->payment( $iProvider, array( 'account' => substr( $iPhone, 1 ), 'comment' => $sComment ), $dAmount );
    }
    public function isSMSAcive() {
        $sResponse = $this->curl( 'options/security.action' );
        $aExplode = explode( 'SMS_CONFIRMATION', $sResponse );
        if( count( $aExplode ) < 3 )
            throw new Exception( 'error check sms confirmation' );
        return strpos( $aExplode[1], 'display' ) === false;
    }
    public function createEgg( $dAmount, $sComment ) {
        return $this->payment( 22496, array( 'account' => '708', 'comment' => $sComment, 'to_account' => '', 'to_account_type' => 'undefind' ), $dAmount );
    }
    public function activateEgg( $sCode ) {
        
        # Инициализация переменных :
        $aResult = array(); # результирующий массив
        
        # Загрузка страницы :
        $sResponse = $this->curl( 'user/eggs/activate/content/form.action', array( 'code' => $sCode ) );
        
        # Преобразование ответа в массив :
        $aResponse = json_decode( $sResponse, true );
        
        # Загрузка страницы :
        $sResponse = $this->curl( 'user/eggs/activate/content/form.action', array( 'code' => $sCode, 'token' => $aResponse['data']['token'] ) );

        # Если зачисления не произошло :
        if( mb_substr_count( $sResponse, $sCode ) != 2 ) {
            
            if( count( $aExplode = explode( '<p>', $sResponse ) ) < 3 )
                throw new Exception( 'undefined error' );
            $aExplode = explode( '</', $aExplode[2] );
            throw new Exception( trim( strip_tags( $aExplode[0] ) ) );
        }
        
        # Парсинг суммы :
        if( count( $aExplode = explode( 'на сумму', $sResponse ) ) < 2 )
            throw new Exception( 'error parse amount' );
        $aExplode = explode( 'руб', $aExplode[1] );
        $aResult['dAmount'] = preg_replace( '/[^0-9\.]+/', '', str_replace( ',', '.', trim( $aExplode[0] ) ) ) - 0;
        
        # Парсинг примечания :
        if( count( $aExplode = explode( 'Комментарий к переводу', $sResponse ) ) < 2 )
            throw new Exception( 'error parse comment' );
        $aExplode = explode( '</p>', $aExplode[1] );
        $aResult['sComment'] = trim( strip_tags( $aExplode[0] ) );

        # Подтверждение :
        $aResponse = json_decode( $this->curl( 'user/eggs/activate/content/activate.action', array( 'code' => $sCode ) ), true );
        
        # Проверка подтверждения :
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['value'] ) || !isset( $aResponse['code']['_name'] ) || $aResponse['code']['value'] != '0' || $aResponse['code']['_name'] != 'NORMAL' )
            throw new Exception( 'bad server answer: '.var_export( $aResponse, true ) );

        return $aResult;
    }
    
    # Метод : запрос на смену пароля.
    # Возвращает : идентификатор запроса.
    public function requestChangePassword() {
        
        # Загружаем страницу :
        $this->curl( 'options/password.action' );
        
        # Загружаем страницу :
        $aResponse = json_decode( $this->curl( 'user/person/change/password.action' ), true );
        
        # Инициализация переменных :
        $iIdentifier = isset( $aResponse['identifier'] ) ? $aResponse['identifier'] - 0 : 0;
        
        # Если поле <= 0 :
        if( $iIdentifier <= 0 )
            throw new Exception( 'field identifier not found' );
        
        # Загружаем страницу :
        $this->curl( 'user/confirmation/form.action', array(
            'identifier' => $iIdentifier,
            'type' => 'PASSWORD_CHANGE'
        ) );
        
        return $iIdentifier;
    }
    
    # Метод : подтверждение смены пароля.
    # Принимает : идентификатор запроса, старый пароль, новый пароль, код с sms.
    public function progressChangePassword( $iIdentifier, $sOldPassword, $sNewPassword, $iCode ) {
        
        # Загружаем страницу :
        $aResponse = json_decode( $this->curl( 'user/confirmation/confirm.action', array(
            'code' => $iCode,
            "data['newPassword']" => $sNewPassword,
            "data['oldPassword']" => $sOldPassword,
            "data['period']" => 4,
            'identifier' => $iIdentifier,
            'type' => 'PASSWORD_CHANGE'
        ) ), true );
        
        # Проверка на наличие ошибок :
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['value'] ) || $aResponse['code']['value'] != 0 )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : json_encode( $aResponse ) );
    }
   
    # Метод : запрос на отключение SMS подтверждения операций.
    # Принимает : требуется отключить?
    # Возвращает : идентификатор подтверждения.
    public function requestConfirmPayments( $bOff = true ) {
        
        # Загрузка страницы :
        $this->curl( 'settings/options/security.action' );
        
        # Загрузка страницы :
        $aResponse = json_decode( $this->curl( 'user/person/change/security.action', array(
            'type' => 'SMS_CONFIRMATION',
            'value' => $bOff ? 'false' : 'true' 
        ) ), true );
        
        # Проверка ответа :
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['value'] ) || $aResponse['code']['value'] != 7 || !isset( $aResponse['data'] ) || !isset( $aResponse['data']['token'] ) )
            throw new Exception( json_encode( $aResponse ) );
        
        # Инициализация переменных :
        $sToken = $aResponse['data']['token'];
        
        # Загрузка страницы :
        $aResponse = json_decode( $this->curl( 'user/person/change/security.action', array(
            'token' => $sToken,
            'type' => 'SMS_CONFIRMATION',
            'value' => $bOff ? 'false' : 'true'
        ) ), true );
        
        # Проверка ответа :
        if( isset( $aResponse['code'] ) && isset( $aResponse['code']['value'] ) && $aResponse['code']['value'] == 0 )
            return true;
        
        # Проверка ответа :
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['value'] ) || $aResponse['code']['value'] != 4 || !isset( $aResponse['identifier'] ) || $aResponse['identifier'] <= 0 )
            throw new Exception( json_encode( $aResponse ) );
        
        # Инициализация переменных :
        $iIdentifier = $aResponse['identifier'];
        
        # Загрузка страницы :
        $this->curl( 'user/confirmation/form.action', array(
            'identifier' => $iIdentifier,
            'token' => $sToken,
            'type' => 'SMS_CONFIRMATION',
            'value' => $bOff ? 'false' : 'true'
        ) );
        
        return $iIdentifier;
    }
    
    # Метод : подтверждение отключения SMS подтверждения операций.
    # Принимает : идентификатор запроса, код с sms.
    public function progressConfirmPayments( $iIdentifier, $iCode ) {
        
        # Загрузка страницы :
        $aResponse = json_decode( $this->curl( 'user/confirmation/confirm.action', array(
            'code' => $iCode,
            'identifier' => $iIdentifier,
            'type' => 'SMS_CONFIRMATION'
        ) ), true );
        
        # Проверка на наличие ошибок :
        if( !isset( $aResponse['code'] ) || !isset( $aResponse['code']['value'] ) || $aResponse['code']['value'] != 0 )
            throw new Exception( isset( $aResponse['message'] ) ? $aResponse['message'] : json_encode( $aResponse ) );
    }
    
    # Метод : SMS подтверждение операции.
    public function paymentSMSConfirm( $iCode ) {
        
        # Загрузка страницы :
        $aResponse = json_decode( $this->curl( 'user/payment/form/state.action', array(
            'confirmationCode' => $iCode,
            'protected' => 'true',
            'state' => 'PAY'
        ) ), true );
        
        # Загрузка страницы :
        $sResponse = $this->curl( 'user/payment/form/state.action', array(
            'confirmationCode' => $iCode,
            'protected' => 'true',
            'state' => 'PAY',
            'token' => $aResponse['data']['token']
        ) );

        # Если платеж был не успешен :
        if( mb_strpos( $sResponse, 'transaction":"' ) === false ) {
            if( mb_strpos( $sResponse, 'class="errorElement"' ) !== false ) {
                $aExplode = explode( 'class="errorElement">', $sResponse );
                $aExplode = explode( '</', $aExplode[1] );
                throw new Exception( trim( $aExplode[0] ) );
            }
            if( count( $sMessage = explode( '<p>', $sResponse ) ) < 2 )
                throw new Exception( 'unknown error' );
            $sMessage = explode( '</p>', $sMessage[1] );
            throw new Exception( $sMessage[0] );
        }
        
        # Если это яйцо :
        if( count( $aExplode = explode( 'Код ваучера:', $sResponse ) ) > 1 ) {
            $aExplode = explode( '<', $aExplode[1] );
            return trim( $aExplode[0] );
        }
        
        # Парсинг № транзакции :
        $aExplode = explode( 'transaction":"', $sResponse );
        $aExplode = explode( '"', $aExplode[1] );
        return $aExplode[0];
    }
    
    # Метод : очистка cookie.
    public function clearCookie() {
        
        # Очистка содержимого файла :
        file_put_contents( $this->sCookieFile, '' );
    }
}