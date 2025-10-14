function beprint(myframe) {
   //alert(myframe);
    // Main_Frame ist der Name des Quelltext-Frames
    myframe.parent.frames.list_frame.focus();
    // Diese Zeile ist fuer den Internet Explorer, da er
    // normalerweise trotz richtiger Angabe nur das fokusierte
    // Frame-Fenster druckt.
    parent.frames.list_frame.print();
}

$(document).ready( function() {
    $('.copy').on('click', function(e) {
        $('.cart-disposed-value').each(function( ele ) {
            uid = $(this).attr('id');
            console.log('uid'+uid);
            //uid = uid.replace('cart-disposed-value-uid-','');
            //$('#club-disposed-value-uid-'+uid).val($('#cart-disposed-value-uid-'+uid).val())
        });
    });
});

