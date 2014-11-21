$(function(){
    console.log('Pillar loaded ...');   // DEBUG
    
    // indien JS aanwezig, maak loginmenu zichtbaar en standaardlogin onzichtbaar
    $('#JS_menuLogin').show();
    $('#NOJS_menuLogin').hide();
    
}); // einde document load

// - visualcheck functie voor window resize (pads, smartphones)

function visualCheck() {
    // een visuele check die items plaatst en verwijdert al naargeland de grootte van het scherm
    //console.log("Window width: " + $(window).width() + "px");   // DEBUG
    //console.log("Window height: " + $(window).height() + "px"); // DEBUG
    
    
    if ($(window).width() > 767) {
        
        // verberg navbar
	$(".navbar").hide();
	// fade in bij scroll
        $(window).scroll(function () {
            // set distance user needs to scroll before we fadeIn navbar
            //console.log($(window).height());
            if ($(this).scrollTop() >= $(window).height()) {
                    $('.navbar').fadeIn();
            } else {
                    $('.navbar').fadeOut();
            }
        });
        
        // toon alle sm-vis-hide
        $('.sm-vis-hide').show();
        // verberg alle sm-vis-show
        $('.sm-vis-show').hide();
        
        // glyphicons
        $('.glyphicon').show();
        $('.backmenu .glyphicon').css({ display: "block" });
    } else {
        // verberg alle sm-vis-hide
        $('.sm-vis-hide').hide();
        // toon alle sm-vis-show
        $('.sm-vis-show').show();
        
        // glyphicons
        $('.glyphicon').hide();
        
        // navbar
        $(".navbar").show();
        $(window).unbind('scroll');
        
    }
}

