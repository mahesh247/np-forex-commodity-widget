jQuery(function($){
	$('.commodity-widget .tabs-nav ul.ui-tabs-nav li a').on('click', function(e){
		e.preventDefault();

		if( !$(this).parent().hasClass('ui-state-active') ){
			$('.ui-tabs-tab').removeClass('ui-state-active');
			$('.ui-tabs-panel').removeClass('active-tab').fadeOut(0);

			$(this).parent().addClass('ui-state-active');;


			var anchorAttr = $(this).attr('href');

			$(anchorAttr).addClass('active-tab').fadeOut(0).fadeIn(200);
		}

	});
});