$( function()
{
    $( 'tr[data-href]' ).click( function( e )
    {
        if ( !$( e.target ).is( 'input' ) && !$( e.target ).is( 'label.checkbox' ) && !$( e.target ).is( 'a' ) )
        {
            window.location = $( e.target ).closest( 'tr' ).data( 'href' );
        };
    });
});

