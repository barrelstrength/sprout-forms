
function getContentHeight() {
	var viewHeight = $( window ).height();
	
	var viewHeight = (viewHeight/10)*6.5;
	
	if(viewHeight > 500) {
	
		$("#content").css({
			"height": viewHeight
		});
		setTimeout(function(){api.reinitialise()}, 400);
		
	}
}

var element = $('#content').jScrollPane({
		showArrows: true,
		hideFocus: true
	});
	
	var api = element.data('jsp');
	
	var horizontalAmmt = api.getContentPositionX();
	
	var horizontalAmmt = -horizontalAmmt;

$(function()
{
	$('#content')
			.bind(
				'jsp-scroll-x',
				function(event, scrollPositionX, isAtLeft, isAtRight)
				{
				
	
	
					$(".left-border").css({
						"left" : scrollPositionX
						
					});
					
					$(".date").css({
						"left" : scrollPositionX + 3
					});
					
					$("table#entries td.delete").css({
						"right" : -scrollPositionX
						
					});
					
				}
	)});
	
$(document).ready(function() {

	getContentHeight();


	
});

$( window ).resize(function() {
	getContentHeight();
	
	
});