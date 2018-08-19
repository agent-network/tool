<?php
    return [
        'common' => [
            'daytime' => 86400,
            'google' => [
                'url_api' => [
                    'url' => 'https://www.googleapis.com/urlshortener/v1/url?key=',
                    'key' => 'AIzaSyBRP5fzk_xtaqGNz9WzDDpPbEgDFecDZjk'
                ],
            ],
            'password' => [
                'string' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789',
                'length' => 10,
            ],
            'gender' => [
                'men'   => [
                    'id'   => 1,
                    'name' => '男性',
                ],
                'women' => [
                    'id'   => 2,
                    'name' => '女性',
                ],
            ],
            'flag' => [
                'on'  => 1,
                'off' => 0,
            ],
        ],
        'mail_delivery' => [
            'affiliate' => [
                'tag' => "\n\n" . '<img src="' . env( 'APP_URL' ) . '/mail/open/{{id}}" alt="" width="1" height="1">',
            ],
            'common' => [
                'attachment_file_number' => 3,
                'temporary_path'         => dirname( dirname( __FILE__ ) ) . '/storage/uploads/mail_delivery/temporary/',
                'attachment_path'        => dirname( dirname( __FILE__ ) ) . '/storage/uploads/mail_delivery/attachment/',
                'csv_path'               => dirname( dirname( __FILE__ ) ) . '/storage/uploads/mail_delivery/csv/',
            ],
            'delivery_type' => [
                'text' => [
                    'id'   => 1,
                    'name' => 'テキスト',
                ],
                'html' => [
                    'id'   => 2,
                    'name' => 'HTML',
                ],
            ],
            'reserve_flg' => [
                'on' => [
                    'id'   => 1,
                    'name' => '予約配信',
                ],
                'off' => [
                    'id'   => 2,
                    'name' => '即時配信',
                ],
            ],
            'delivery_status' => [
                'edit' => [
                    'id'   => 0, 
                    'name' => '配信準備中',
                ],
                'wait' => [
                    'id'   => 1, 
                    'name' => '配信待ち',
                ],
                'progress' => [
                    'id'   => 2,
                    'name' => '配信中',
                ],
                'ok' => [
                    'id'   => 3,
                    'name' => '配信済み',
                ],
                'cancel' => [
                    'id'   => 4,
                    'name' => 'キャンセル',
                ],
            ],
            'csv' => [
                'subject' => [
                    '通番',
                    '配信ID',
                    'メールアドレス',
                    '氏名(姓)',
                    '氏名(名)',
                    '送信状態',
                    '開封状態',
                    '開封日時',
                ],
            ],
        ],
        'auth' => [
            'level' => [
                'system' => [
                     'id'   => '1',
                     'name' => 'システム',
                ],
                'normal' => [
                     'id'   => '2',
                     'name' => '一般',
                ],
            ],
        ],
        'page' => [
            'number20' => 20,
            'number30' => 30,
            'number50' => 50,
        ],
        'file' => [
            'encode' => [
                'code1' => 'SJIS',
                'code2' => 'UTF-8',
            ],
            'download' => [
                'csv_path' => dirname( dirname( __FILE__ ) ) . '/storage/downloads/csv/',
            ],
        ],
        'mail' => [
            'common' => [
                'from' => [
                    'address'  => 'noreply@agent-network.com',
                    'nickname' => 'エージェント',
                ],
                'subject' => [
                    'prefix' => '',
                ],
            ],
            'zipfile' => [
                'password' => [
                    'subject'  => '圧縮ファイルのパスワードの送付',
                    'template' => 'email/zipfile/password',
                ],
            ],
            'manage' => [
                'new' => [
                    'subject'  => '管理者登録が完了しました。',
                    'template' => 'email/manage/new',
                ],
                'password' => [
                    'subject'  => 'パスワードを再設定しました。',
                    'template' => 'email/manage/password',
                ],
            ],
        ],
    ];
