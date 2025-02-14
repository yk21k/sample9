<?php
 
return [
  'format' => [
    'date' => 'y/m/d',
    'datetime' => 'y/m/d H:i:s',
  ],
  // 0:仮登録 1:本登録 2:メール認証済 9:退会済
    'USER_STATUS' => ['PRE_REGISTER' => '0', 'REGISTER' => '1', 'MAIL_AUTHED' => '2', 'DEACTIVE' => '9',],
];