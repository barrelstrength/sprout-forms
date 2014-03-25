var element = $('#tab-entries').jScrollPane({
	showArrows: true,
	hideFocus: true
});
var api = element.data('jsp');
var horizontalAmmt = api.getContentPositionX();
var horizontalAmmt = -horizontalAmmt;
var dateHeight = $("td.date").innerHeight();

// Avoid future issues with pane padding changing by making content negative margin dynamic
var panePaddingLeft = $('.pane').css('padding-left');
var panePaddingRight = $('.pane').css('padding-right');
var panePaddingBottom = $('.pane').css('padding-bottom');

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

function checkSelectedTab() {
	if ($("#tab-formEntries").hasClass("sel")) {
		$('.bsd-branding').addClass('hidden');
		
		$('#content').css({
			'margin-left' : '-' + panePaddingLeft,
			'margin-right' : '-' + panePaddingRight,
			'margin-bottom' : '-' + panePaddingBottom
		});
	}
}
	
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
	checkSelectedTab();
	
	$('#tab-entries').jScrollPane({
		showArrows: true,
		hideFocus: true
	});
	
	$("th.date").css({
		"height" : dateHeight
	});
	
	$("th > div").each(function() {
		if ($(this).parent("th").hasClass("padding")) {
		
			var divWidth = ($('.date').innerWidth()) + ($('.left-border').innerWidth());
		} else {
			var divWidth = $(this).parent("th").innerWidth();
		}
	
		
		var divHeight = $(this).parent("th").innerHeight();
		
		$(this).css({
			"width" : divWidth,
			"height": divHeight
		});
	});
	
	$('a').click(function(event) {

		var currentUrl = String(event.currentTarget);
		var currentSegment = currentUrl.split("#")[1];
		
		//alert(currentSegment);
	
		if (currentSegment == "tab-entries") {
			$('.bsd-branding').addClass('hidden');
			$('#content').css({
				'margin' : '0 -24px -24px'
				
			});
			
		} else {
			$('.bsd-branding').removeClass('hidden');
		}
	});
});

$( window ).resize(function() {
	getContentHeight();
});