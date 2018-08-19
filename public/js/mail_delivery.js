$( function()
{
    // 「全てにチェック」のチェックボックスをチェックしたら発動
    $( '#all' ).change( function()
    {
        // もし「全てにチェック」のチェックが入ったら
        if ( $( this ).prop( 'checked' ) )
        {
            // チェックを付ける
            $( 'input[name="mail_delivery_ids[]"]' ).prop( 'checked', true );
        // もしチェックが外れたら
        }
        else
        {
            // チェックを外す
            $( 'input[name="mail_delivery_ids[]"]' ).prop( 'checked', false );
        }
    });
});


setAction = function( url )
{
    document.form1.action = url;
};


setModeAndSubmit = function( mode, keyname, keyid )
{
    switch ( mode )
    {
        case 'test_delivery':
            if ( !window.confirm( 'テスト配信を行います。\nテストメールはCSVのtest欄にフラグの立っている行の\nメールアドレスへ配信します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'complete':
            if ( !window.confirm( '配信設定を完了します。完了すると情報の編集はできません。\nまた配信設定が「即時配信」を選択している場合、\n配信設定完了と同時にメールが配信されますのでご注意ください。' ) )
            {
                return;
            }
            break;
        case 'cancel':
            if ( !window.confirm( 'メール配信をキャンセルします。よろしいですか？\n※キャンセルしても情報編集は可能になりません。' ) )
            {
                return;
            }
            break;
        case 'commit':
            if ( !window.confirm( 'メール配信設定します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'delete':
            if ( !window.confirm( '設定情報を削除します。よろしいですか？' ) )
            {
                return;
            }
            break;
        default:
            break;
    }
    document.form1.mode.value = mode;
    if ( keyname !== undefined && keyname !== "" && keyid !== undefined && keyid !== "" )
    {
        document.form1[keyname].value = keyid;
    }
    document.form1.submit();
};
