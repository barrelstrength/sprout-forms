
function getContentHeight() {
	var viewHeight = $( window ).height();
	
	var viewHeight = (viewHeight/10)*6.5;
	
	if (viewHeight > 500) {
	
		$("#tab-entries").css({
			"height": viewHeight
		});
		var posX = api.getContentPositionX();
		
		setTimeout(function(){api.scrollToX(0, 0)}, 100);
		setTimeout(function(){api.reinitialise()}, 400);
		
		
	} else {
		$("#tab-entries").css({
			"height": "500px"
		});
	}
}

var element = $('#tab-entries').jScrollPane({
	showArrows: true,
	hideFocus: true
});

var api = element.data('jsp');

var horizontalAmmt = api.getContentPositionX();

var horizontalAmmt = -horizontalAmmt;

var dateHeight = $("td.date").innerHeight();
	
	
	
$(function() {
	$('#tab-entries').bind({
		'jsp-scroll-x': function(event, scrollPositionX, isAtLeft, isAtRight) {
		
			

			$(".left-border").css({
				"left" : scrollPositionX
				
			});
			
			$(".date").css({
				"left" : scrollPositionX + 3
			});
			
			$("table#entries td.delete").css({
				"right" : (-scrollPositionX)+16
				
			});
			
			
		},
		
		'jsp-scroll-y': function(event, scrollPositionY, isAtTop, isAtBottom) {
			$("th div").css({
				"top" : scrollPositionY
			});
			
		}
	})
});
	
$(document).ready(function() {
	
	getContentHeight();
	
	$('#tab-entries').jScrollPane({
		showArrows: true,
		hideFocus: true
	});
	
	$("th.date").css({
		"height" : dateHeight
	});
	
	$("th > div").each(function() {
		var divWidth = $(this).parent("th").innerWidth();
		var divHeight = $(this).parent("th").innerHeight();
		
		$(this).css({
			"width" : divWidth,
			"height": divHeight
		});
		
	});
	
});

$( window ).resize(function() {
	getContentHeight();
	
	
});