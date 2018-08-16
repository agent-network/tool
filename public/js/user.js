$( function()
{
    // 「全てにチェック」のチェックボックスをチェックしたら発動
    $( '#all' ).change( function()
    {
        // もし「全てにチェック」のチェックが入ったら
        if ( $( this ).prop( 'checked' ) )
        {
            // チェックを付ける
            $( 'input[name="user_ids[]"]' ).prop( 'checked', true );
        // もしチェックが外れたら
        }
        else
        {
            // チェックを外す
            $( 'input[name="user_ids[]"]' ).prop( 'checked', false );
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
        case 'basis_new':
            if ( !window.confirm( '基本情報を新規登録します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'basis_edit':
            if ( !window.confirm( '基本情報を編集します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'delete':
            if ( !window.confirm( 'ユーザを削除します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'password':
            if ( !window.confirm( 'パスワードを再発行します。よろしいですか？' ) )
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
