$( function()
{ 
    // 「全てにチェック」のチェックボックスをチェックしたら発動
    $( '#all' ).change( function()
    {
        // もし「全てにチェック」のチェックが入ったら
        if ( $( this ).prop( 'checked' ) )
        {
            // チェックを付ける
            $( 'input[name="questionnaire_ids[]"]' ).prop( 'checked', true );
        // もしチェックが外れたら
        }
        else
        {
            // チェックを外す
            $( 'input[name="questionnaire_ids[]"]' ).prop( 'checked', false );
        }
    });
});


setModeAndSubmit = function( mode, keyname, keyid )
{
    switch ( mode )
    {
        case 'answer_reset':
            if ( !window.confirm( '回答をリセットします。\nリセット以前の回答をダウンロードすることはできませんので\n先にダウンロードすることをお勧めします。\nリセットを実行してよろしいですか？' ) )
            {
                return;
            }
            break;
        case 'new':
            if ( !window.confirm( 'アンケートを新規登録します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'edit':
            if ( !window.confirm( 'アンケートを編集します。よろしいですか？' ) )
            {
                return;
            }
            break;
        case 'stop':
            if ( !window.confirm( 'アンケートを中止します。再開する事は出来ません。\nよろしいですか？' ) )
            {
                return;
            }
            break;
        case 'delete':
            if ( !window.confirm( 'アンケートを削除します。\nアンケートの回答も全て削除され元には戻せません。\nよろしいですか？' ) )
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


changeQuestionType = function( id )
{
    var type = $( '#question_type\\[' + id + '\\]' ).val();

    var block = $( '#question_block\\[' + id + '\\]' );
    for ( var i = 1; i < 20; i++ )
    {
        if ( block.find( '#question_choice_block\\[' + id + '\\]\\[' + i +'\\]' ) )
        {
            block.find( '#question_choice_block\\[' + id + '\\]\\[' + i +'\\]' ).remove();
        }
    }
    if ( type == 1 || type == 2 || type == 6 )
    {
        block.find( '.a_text' ).css( 'display', 'none' );
        block.find( '.b_text' ).css( 'display', 'none' );
    }
    else
    {
        block.find( '.a_text' ).css( 'display', 'inline' );
        block.find( '.b_text' ).css( 'display', 'none' );
    }
};


cloneQuestion = function()
{   
    for ( var i = 0; i < 100; i++ )
    {   
        if ( 0 >= $('#question_block\\[' + i + '\\]').length )
        {   
            cnt = i - 1;
            break;
        }
    }

    var original = $('#question_block\\[' + cnt + '\\]');
    cnt++;

    original
        .clone()
        .hide()
        .insertAfter( original )
        .attr( 'id', 'question_block[' + cnt + ']' ) // クローンのid属性を変更。
        .end();
        
    var clone = $( '#question_block\\[' + cnt + '\\]' );
    for ( var i = 1; i < 20; i++ ) 
    {
        if ( clone.find( '#question_choice_block\\[' + ( cnt - 1 ) + '\\]\\[' + i +'\\]' ) )
        {
            clone.find( '#question_choice_block\\[' + ( cnt - 1 ) + '\\]\\[' + i +'\\]' ).remove();
        }
    }
    clone.find( '#question_choice_block\\[' + ( cnt - 1 ) + '\\]\\[0\\]' )
         .attr( 'id', 'question_choice_block[' + cnt + '][0]' );
    clone.find( '#choice_id\\[' + ( cnt - 1 ) + '\\]\\[0\\]' )
         .attr( 'id', 'choice_id[' + cnt + '][0]' )
         .attr( 'name', 'choice_id[' + cnt + '][0]' ).val( '' );
    clone.find( '#question_choice\\[' + ( cnt - 1 ) + '\\]\\[0\\]' )
         .attr( 'id', 'question_choice[' + cnt + '][0]' )
         .attr( 'name', 'question_choice[' + cnt + '][0]' ).val( '' );
    clone.find( '#question_choice_minus\\[' + ( cnt - 1 ) + '\\]\\[0\\]' )
         .attr( 'id', 'question_choice_minus[' + cnt + '][0]' )
         .attr( 'onclick', 'deleteChoice(' + cnt + ',0); return false;' );
    clone.find( '#question_choice_plus_button' )
         .attr( 'onclick', 'cloneChoice(' + cnt + '); return false;' );

    clone.find( '#question_id\\[' + ( cnt - 1 ) + '\\]' )
         .attr( 'id', 'question_id[' + cnt + ']' )
         .attr( 'name', 'question_id[' + cnt + ']' ).val( '' );
    clone.find( '#question_title\\[' + ( cnt - 1 ) + '\\]' )
         .attr( 'id', 'question_title[' + cnt + ']' )
         .attr( 'name', 'question_title[' + cnt + ']' ).val( '' );
    clone.find( '#question_required\\[' + ( cnt - 1 ) + '\\]' )
         .attr( 'id', 'question_required[' + cnt + ']' )
         .attr( 'name', 'question_required[' + cnt + ']' ).checked = false;
    clone.find( '#question_description\\[' + ( cnt - 1 ) + '\\]' )
         .attr( 'id', 'question_description[' + cnt + ']' )
         .attr( 'name', 'question_description[' + cnt + ']' ).val( '' );
    clone.find( '#question_type\\[' + ( cnt - 1 ) + '\\]' )
         .attr( 'id', 'question_type[' + cnt + ']' )
         .attr( 'name', 'question_type[' + cnt + ']' )
         .attr( 'onchange', 'changeQuestionType(' + cnt + '); return false;' ).val( '1' );
    clone.find( '.question_block_delete' )
         .attr( 'onclick', 'deleteQuestion(' + cnt + '); return false;' )
         .css( 'display', 'inline' );
    clone.find( '.required_label' ).attr( 'for', 'question_required[' + cnt + ']' );
    clone.find( '.a_text' ).css( 'display', 'none' );
    clone.find( '.error' ).remove();
    clone.slideDown( 'fast' );
};


deleteQuestion = function( id )
{  
    var original = $('#question_block\\[' + id + '\\]');
    original.remove();
};


var cnt = 0;
cloneChoice = function( id )
{
    for ( var i = 0; i < 20; i++ )
    {   
        if ( 0 >= $('#question_choice_block\\[' + id + '\\]\\[' + i + '\\]').length )
        {   
            cnt = i - 1;
            break;
        }
    }

    var original = $('#question_choice_block\\['+ id +'\\]\\[' + cnt + '\\]');
    cnt++;

    original
        .clone()
        .hide()
        .insertAfter( original )
        .attr( 'id', 'question_choice_block[' + id + '][' + cnt + ']' ) // クローンのid属性を変更。
        .css( 'display', 'inline' )
        .end();

    var clone = $( '#question_choice_block\\[' + id + '\\]\\[' + cnt + '\\]' );
    clone.find( '.choice_id' ).attr( 'id', 'choice_id[' + id + '][' + cnt + ']' )
                              .attr( 'name', 'choice_id[' + id + '][' + cnt + ']' ).val( '' );
    clone.find( '.choice' ).attr( 'id', 'question_choice[' + id + '][' + cnt + ']' )
                           .attr( 'name', 'question_choice[' + id + '][' + cnt + ']' ).val( '' );
    clone.find( '.minus' ).attr( 'id', 'question_choice_minus[' + id + '][' + cnt + ']' )
                          .attr( 'onclick', 'deleteChoice(' + id + ',' + cnt + '); return false;' )
                          .css( 'display', 'inline' );
    clone.slideDown( 'fast' );
};


deleteChoice = function( id, cnt )
{   
    var original = $('#question_choice_block\\[' + id + '\\]\\[' + cnt + '\\]');
    original.remove();
};

